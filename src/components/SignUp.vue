<template>
  <div class="login">
    <h3>Create a new account</h3>
    <input v-model="email" type="text" placeholder="Email"/>
    <input v-model="password" type="password" placeholder="Password"/>
    <button v-on:click="signUp">Sign Up</button>
    <p>or go back to <router-link to="/login">login.</router-link></p>
  </div>
</template>

<script>
import firebase from 'firebase';

export default {
  name: 'signUp',
  data() {
    return {
      email: '',
      password: '',
    };
  },
  methods: {
    signUp() {
      firebase
        .auth()
        .createUserWithEmailAndPassword(this.email, this.password)
        .then(() => alert('account created'), err => alert(err.message)); // eslint-disable-line
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
