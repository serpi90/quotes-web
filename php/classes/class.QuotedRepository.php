<?php
class QuotedRepository {

  private $db_connection;
  private $quoted;
  private $filteredQuoted;
  private $statements;

  public function __construct( $db_connection ) {
    $this->db_connection = $db_connection;
    $this->quoted = array( );
    $this->filteredQuoted = array( );
    $this->statements = array( );
    $this->fetchAllQuoted( );
  }
  
  public function __destruct( ) {
    foreach( $this->statements as $s ) {
      $s->close( );
    }
  }

  private function fetchAllQuoted( ) {
    $result = $this->db_connection->query("SELECT * from Quoted ORDER BY name ASC");
    while( $quoted = $result->fetch_object( 'Quoted' ) ) {
      $this->quoted[ $quoted->idQuoted( ) ] = $quoted;
      if( $quoted->display( ) ) {
        $this->filteredQuoted[ $quoted->idQuoted( ) ] = $quoted;
      }
    }
    $result->free( );
    $result = $this->db_connection->query("SELECT * from QuotedAlias");
    while( $alias = $result->fetch_object( ) ) {
      $this->quoted[ $alias->idQuoted ]->addAlias( $alias->alias );
    }
  }

  public function getQuotedWithId( $idQuoted ) {
    if( !isset( $this->quoted[ $idQuoted ] ) ) {
      throw new OutOfRangeException( $idQuoted );
    }
    return $this->quoted[ $idQuoted ];
  }

  public function getFilteredQuoted( ) {
    return $this->filteredQuoted;
  }

  public function getAllQuoted( ) {
    return $this->quoted;
  }

  public function addQuoted( $name, $aliases ) {
    $namesToValidate = $aliases;
    array_push( $namesToValidate, $name );
    $this->validateNames( $namesToValidate );
    $idQuoted = $this->insertQuoted( $name );
    $this->insertAliases( $idQuoted, $aliases );
    return $idQuoted;
  }

  private function validateNames( $names ) {
    $errors = '';
    foreach( $names as $name ) {
      if( $this->existsQuotedNameOrAlias( $name ) ) {
        $errors .= ( empty( $errors ) ? '' : ' / ' ) . $name;
      }
    }
    if( !empty( $errors ) ) {
      throw new DomainException( $errors );
    }
  }
  
  private function insertQuoted( $name ) {
    if( !isset($this->statements['insertQuoted']) or $this->statements['insertQuoted'] === null ) {
      $this->statements['insertQuoted'] = $this->db_connection->prepare("INSERT INTO Quoted (name) VALUES (?)");
    }
    $this->statements['insertQuoted']->bind_param( 's', $name ) or die( $this->statements['insertQuoted']->error );
    $this->statements['insertQuoted']->execute( ) or die( $this->statements['insertQuoted']->error );

    return $this->statements['insertQuoted']->insert_id;
  }
  
  private function insertAliases( $idQuoted, $aliases ) {
    foreach( $aliases as $alias ) {
      if( !isset($this->statements['insertAlias']) or $this->statements['insertAlias'] === null ) {
        $this->statements['insertAlias'] = $this->db_connection->prepare("INSERT INTO QuotedAlias (idQuoted,alias) VALUES (?,?)");
      }
      $this->statements['insertAlias']->bind_param( 'ss', $idQuoted, $alias ) or die( $this->statements['insertAlias']->error );
      $this->statements['insertAlias']->execute( ) or die( $this->statements['insertAlias']->error );
    }
  }
  
  private function quotedByNameOrAlias( $nameOrAlias ) {
    foreach( $this->quoted as $q ) {
      if( strtoupper( $q->name( ) ) == strtoupper( $nameOrAlias ) ) {
        return $q;
      } else {
        foreach( $q->alias( ) as $alias ) {
          if( strtoupper( $alias ) == strtoupper( $nameOrAlias ) ) {
            return $q;
          }
        }
      }
    }
    throw new OutOfRangeException( "$nameOrAlias not in quoted repository." );
  }
  
  private function existsQuotedNameOrAlias( $nameOrAlias ) {
    try {
      $this->quotedByNameOrAlias( $nameOrAlias );
    } catch ( OutOfRangeException $e ) {
      return false;
    }
    return true;
  }
  
  private function updateQuoted( $idQuoted, $name, $active ) {
    if( !isset($this->statements['updateQuoted']) or $this->statements['updateQuoted'] === null ) {
      $this->statements['updateQuoted'] = $this->db_connection->prepare("UPDATE Quoted SET name = ?, display = ? WHERE idQuoted = ?");
    }
    $this->statements['updateQuoted']->bind_param( 'sii', $name, $active, $idQuoted ) or die( $this->statements['updateQuoted']->error );
    $this->statements['updateQuoted']->execute( ) or die( $this->statements['updateQuoted']->error );
  }
  
  public function removeAliasesOf( $idQuoted ) {
    if( !isset($this->statements['removeAliases']) or $this->statements['removeAliases'] === null ) {
        $this->statements['removeAliases'] = $this->db_connection->prepare("DELETE FROM QuotedAlias WHERE idQuoted = ?");
      }
      $this->statements['removeAliases']->bind_param( 'i', $idQuoted ) or die( $this->statements['removeAliases']->error );
      $this->statements['removeAliases']->execute( ) or die( $this->statements['removeAliases']->error );
      $quoted = $this->getQuotedWithId( $idQuoted )->clearAliases( );
  }
  
  public function editQuoted( $idQuoted, $name, $active ) {
    $quoted = $this->getQuotedWithId( $idQuoted );
    if( $name !== $quoted->name( ) && $this->existsQuotedNameOrAlias( $name ) ) {
      throw new DomainException( $name );
    } else {
      $this->updateQuoted( $idQuoted, $name, $active );
    }
  }
  
  public function editQuotedAliases( $idQuoted, $aliases ) {
    $this->validateNames( $aliases );
    $this->insertAliases( $idQuoted, $aliases );
  }
}