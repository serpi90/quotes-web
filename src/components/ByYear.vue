<template>
  <div class="by-year">
    <h1 class="text-center">{{year}} <small v-if="quotes.length">({{quotes.length}} quotes)</small></h1>
    <input v-model="year" type="number" class="form-control" placeholder="Year" :min="minYear" :max="maxYear">
    <spin-kit v-if="loading"/>
    <ol class="list-group list-group-flush" v-if="!loading">
      <li class="list-group-item" v-for="(quote) in quotes" :key="quote['.key']">
        <blockquote :id="quote.number" class="blockquote">
          <p class="mb-0">{{quote.quote}}</p>
          <p class="blockquote-footer">
            <cite :title="author(quote)">{{signature(quote)}} · {{new Date(quote.date).toISOString().slice(0,10)}}</cite>
          </p>
        </blockquote>
      </li>
    </ol>
  </div>
</template>

<script>
import firebase from 'firebase/app';
import 'firebase/database';

import SpinKit from '@/components/SpinKit';

export default {
  name: 'ByYear',
  components: {
    SpinKit,
  },
  data() {
    return {
      year: new Date().getUTCFullYear(),
      loading: true,
    };
  },
  firebase() {
    return {
      people: firebase.database().ref('people'),
      quotes: {
        source: this.quotesForYear(this.year),
        readyCallback: () => { this.loading = false; },
      },
      firstQuote: firebase.database().ref('quotes')
        .orderByChild('date')
        .limitToFirst(1),
      lastQuote: firebase.database().ref('quotes')
        .orderByChild('date')
        .limitToLast(1),
    };
  },
  watch: {
    year(newYear) {
      this.loading = true;
      this.$unbind('quotes');
      this.$bindAsArray(
        'quotes',
        this.quotesForYear(parseInt(newYear, 10)),
        null,
        () => { this.loading = false; },
      );
    },
  },
  computed: {
    minYear() {
      if (this.firstQuote[0]) {
        return Math.min(new Date(this.firstQuote[0].date).getUTCFullYear(), this.year);
      }
      return this.year;
    },
    maxYear() {
      if (this.lastQuote[0]) {
        return Math.max(new Date(this.lastQuote[0].date).getUTCFullYear(), this.year);
      }
      return this.year;
    },
  },
  methods: {
    quotesForYear(year) {
      return firebase.database()
        .ref('quotes')
        .orderByChild('date')
        .startAt(Date.UTC(year, 0))
        .endAt(Date.UTC(year + 1, 0) - 1);
    },
    signature(quote) {
      return quote.signature || this.author(quote);
    },
    author(quote) {
      return this.people.find(p => p['.key'] === quote.author.toString()).name;
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
h1 {
  margin: 0.5em;
}
.blockquote {
  margin-bottom: 0;
  font-size: 1.2rem;
}
</style>
