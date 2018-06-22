<template>
  <div class="login">
    <h3>Sign In</h3>
    <input v-model="email" type="text" placeholder="Email"/>
    <input v-model="password" type="password" placeholder="Password"/>
    <button v-on:click="signIn">Connection</button>
    <p>You don't have an account? You can <router-link to="/sign-up">create one</router-link></p>
  </div>
</template>

<script>
import firebase from 'firebase';

export default {
  name: 'login',
  data() {
    return {
      email: '',
      password: '',
    };
  },
  methods: {
    signIn() {
      firebase
        .auth()
        .signInWithEmailAndPassword(this.email, this.password)
        .then(() => this.$router.replace('hello'));
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
