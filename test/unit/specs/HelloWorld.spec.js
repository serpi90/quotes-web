import Vue from 'vue';
import ByYear from '@/components/ByYear';

describe('ByYear.vue', () => {
  it('should render correct contents', () => {
    const Constructor = Vue.extend(ByYear);
    const vm = new Constructor().$mount();
    expect(vm.$el.querySelector('.by-year h1').textContent)
      .to.equal('Welcome to Your Vue.js App');
  });
});
