<template>
  <div class="login">
    <div>
      <img id="logo"
        src="../assets/logo.svg"
        onerror="this.src='../assets/logo.png'; this.onerror=null;"/>
    </div>
    <button type="button" class="btn btn-lg btn-primary" v-on:click="signIn">Sign In</button>
  </div>
</template>

<script>
import firebase from 'firebase/app';
import 'firebase/auth';

const googleProvider = new firebase.auth.GoogleAuthProvider();

export default {
  name: 'login',
  data() {
    return {};
  },
  methods: {
    signIn() {
      firebase
        .auth()
        .signInWithRedirect(googleProvider)
        .then(
          result => JSON.stringify(result.user.displayName),
          err => alert(err.message), // eslint-disable-line
        )
        .then(() => this.$router.replace('by-year'));
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
#logo {
  width: 300px;
  max-width: calc(100vw - 6em);
  margin: 3em;
}
</style>
