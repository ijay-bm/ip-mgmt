import { createRouter, createWebHistory } from "vue-router";
import IpAddresses from "@/pages/audit-logs/IpAddresses.vue";
import Users from "@/pages/audit-logs/Users.vue";
import IpManagement from "@/pages/IpManagement.vue";
import Login from "@/pages/Login.vue";
import { useAuthStore } from "@/stores/auth";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      redirect: () => {
        const { isAuthenticated } = useAuthStore();
        return isAuthenticated ? "/ip-management" : "/login";
      },
    },
    { path: "/login", component: Login },
    {
      path: "/ip-management",
      component: IpManagement,
      meta: { requiresAuth: true },
    },
    {
      path: "/audit-logs",
      meta: { requiresAuth: true, requiresSuperAdmin: true },
      children: [
        { path: "users", component: Users },
        {
          path: "ip-addresses",
          component: IpAddresses,
        },
      ],
    },
  ],
});

router.beforeEach((to) => {
  const { isAuthenticated, isSuperAdmin } = useAuthStore();

  if (to.meta.requiresAuth && !isAuthenticated) {
    return { path: "/login" };
  }

  if (to.meta.requiresSuperAdmin && !isSuperAdmin) {
    return { path: "/ip-management" };
  }

  return true;
});

export default router;
