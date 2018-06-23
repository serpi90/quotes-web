import Vue from 'vue';
import VueFire from 'vuefire';
import firebase from 'firebase/app';

import App from './App';
import router from './router';


Vue.config.productionTip = false;

Vue.use(VueFire);

// Initialize Firebase
firebase.initializeApp({
  apiKey: 'AIzaSyA5pDV6eyJ25Z_b35mxk_ytxkqeTkpg1ng',
  authDomain: 'mcp-quotes.firebaseapp.com',
  databaseURL: 'https://mcp-quotes.firebaseio.com',
  projectId: 'mcp-quotes',
  storageBucket: 'mcp-quotes.appspot.com',
  messagingSenderId: '182915642791',
});

/* eslint-disable no-new */
let app;
firebase.auth().onAuthStateChanged(() => {
  if (!app) {
    app = new Vue({
      el: '#app',
      router,
      render: h => h(App),
    });
  }
});
