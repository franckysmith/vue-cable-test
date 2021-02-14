import { createRouter, createWebHistory } from "vue-router";
import Home from "../views/Home.vue";

const routes = [
  {
    path: "/",
    name: "Home",
    component: Home
  },
  {
    path: "/enceinte",
    name: "Enceinte",
    // route level code-splitting
    // this generates a separate chunk (enceinte.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () =>
      import(/* webpackChunkName: "enceinte" */ "../views/Enceinte.vue")
  },
  {
    path: "/cablage",
    name: "Cablage",
    // route level code-splitting
    // this generates a separate chunk (cablage.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () =>
      import(/* webpackChunkName: "cablage" */ "../views/Cablage.vue")
  },
  {
    path: "/listeaffaire",
    name: "Listeaffaire",
    // route level code-splitting
    // this generates a separate chunk listaffaire.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () =>
      import(/* webpackChunkName: "listaffaire" */ "../views/Listeaffaire.vue")
  }
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
});

export default router;
