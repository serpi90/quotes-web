<template>
  <div id="app">
    <nav-bar v-if="loggedIn()"/>
    <main role="main">
      <div class="container">
        <router-view/>
      </div>
    </main>
  </div>
</template>

<script>
import firebase from 'firebase/app';
import 'firebase/auth';
import NavBar from '@/components/NavBar';

export default {
  name: 'App',
  methods: {
    loggedIn() {
      return firebase.auth().currentUser;
    },
  },
  components: {
    NavBar,
  },
};
</script>

<style>
#app {
  font-family: 'Source Sans Pro', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
main {
  height: calc(100vh - 56px);
  overflow-y: auto;
}
</style>
