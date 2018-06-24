<template>
  <div class="login text-center">
    <!-- <div>
      <img id="logo" src="../assets/logo.svg"
        onerror="this.src='../assets/logo.png'; this.onerror=null;"/>
    </div> -->
    <button id="login" type="button" class="btn btn-lg btn-primary"
      v-on:click="signIn">Sign In</button>
  </div>
</template>

<script>
import firebase from 'firebase/app';
import 'firebase/auth';

const googleProvider = new firebase.auth.GoogleAuthProvider();

export default {
  name: 'Login',
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
/* #logo {
  margin: 3em;
  filter: invert(100%);
  height: 15em;
  max-width: calc(100vw - 6em);
  max-height: calc(50vh - 3em - 3em);
} */
#login {
  position: absolute;
  height: 3em;
  top: calc(50vh - 1.5em);
  width: 10em;
  left: calc(50vw - 5em);
}
</style>
