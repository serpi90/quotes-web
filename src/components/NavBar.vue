<template>
  <nav class="navbar navbar-dark navbar-expand-md bg-dark">
    <a class="navbar-brand mr-auto" href="#">
      <img id="brand"
        src="../assets/logo.svg"
        onerror="this.src='../assets/logo.png'; this.onerror=null;"/>
    </a>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="#" v-on:click="logout">Logout</a>
      </li>
    </ul>
  </nav>
</template>

<script>
import firebase from 'firebase/app';
import 'firebase/auth';

export default {
  name: 'NavBar',
  methods: {
    logout() {
      firebase.auth()
        .signOut()
        .then(() => this.$router.replace('login'));
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
#brand {
  filter: invert(100%);
  height: 1.25rem;
}
</style>
