import type { Token, User } from "../types/Auth";
import { defineStore } from "pinia";
import router from "../router";
import axios, { resetSessionState } from "../services/axios";

type LoginResponse = Token & {
  user: User;
};

export const useAuthStore = defineStore("auth", {
  persist: true,

  state: () => {
    return {
      user: null as User | null,
      token: null as Token | null,
    };
  },

  getters: {
    isSuperAdmin: (state) => (state.user?.roles || []).includes("super-admin"),

    accessToken: (state) => state.token?.access_token,

    stateWasPersisted: (state) =>
      !!state.token?.access_token && !!state.user?.id,

    isAuthenticated: (state) => !!state.token?.access_token,
  },

  actions: {
    // *might implement zod
    async login(params: { email: string; password: string }) {
      resetSessionState();

      const response = await axios.post<LoginResponse>("/auth/login", params);

      const { user, ...token } = response.data;

      this.user = user;
      this.token = token;

      if (router.currentRoute.value.redirectedFrom) {
        router.push(router.currentRoute.value.redirectedFrom);
      } else {
        router.push({ path: "/ip-management" });
      }

      return response;
    },

    async me() {
      const response = await axios.get<{ data: User }>("/auth/me");
      this.user = response.data.data;
      return response;
    },

    async logout(callServer = true) {
      if (callServer && this.accessToken) {
        const token = this.accessToken;
        axios
          .post("/auth/logout", null, {
            headers: { Authorization: `Bearer ${token}` },
          })
          .catch(() => {});
      }

      this.user = null;
      this.token = null;

      router.push({ path: "/login" });
    },

    async refresh() {
      const response = await axios.post<Token>("/auth/refresh");
      this.token = response.data;
      return response;
    },
  },
});
