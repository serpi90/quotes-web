import firebase from 'firebase/app';
import 'firebase/auth';
import Vue from 'vue';
import Router from 'vue-router';
import ByYear from '@/components/ByYear';
import Login from '@/components/Login';

Vue.use(Router);

const router = new Router({
  routes: [
    {
      path: '*',
      redirect: '/login',
    },
    {
      path: '/',
      redirect: '/login',
    },
    {
      path: '/login',
      name: 'Login',
      component: Login,
    },
    {
      path: '/by-year',
      name: 'ByYear',
      component: ByYear,
      meta: { requiresAuth: true },
    },
  ],
});

router.beforeEach((to, from, next) => {
  const currentUser = firebase.auth().currentUser;
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  if (requiresAuth && !currentUser) next('login');
  else if (!requiresAuth && currentUser) next('by-year');
  else next();
});

export default router;
