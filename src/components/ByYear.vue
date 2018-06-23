<template>
  <div class="by-year">
    <p>{{ people.length }}</p>
    <ul>
      <li v-for="person in people" class="user" :key="person['.key']">
        1 <span>{{person}}</span>
      </li>
    </ul>
    <button v-on:click="logout">Logout</button>
  </div>
</template>

<script>
import firebase from 'firebase/app';
import 'firebase/auth';
import 'firebase/database';

export default {
  name: 'ByYear',
  data() {
    return {};
  },
  firebase() {
    return {
      people: firebase.database().ref('people'),
      quotes: firebase.database().ref('quotes'),
    };
  },
  methods: {
    logout() {
      firebase.auth().signOut().then(() => this.$router.replace('login'));
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
