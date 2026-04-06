import baseAxios from "axios";
import { createAuthRefresh } from "axios-auth-refresh";
import { useToasterStore } from "@/stores/toaster";
import { useAuthStore } from "../stores/auth";

const axios = baseAxios.create({
  baseURL: import.meta.env.VITE_BACKEND_URL,
});

axios.interceptors.request.use((config) => {
  const accessToken = useAuthStore().token?.access_token;
  const contentType =
    config.data instanceof FormData
      ? "multipart/form-data"
      : "application/json";

  config.headers.set("Accept", "application/json");
  config.headers.set("Content-Type", contentType);
  if (accessToken) config.headers.set("Authorization", `Bearer ${accessToken}`);

  return config;
});

let sessionDead = false;

createAuthRefresh(
  axios,
  async () => {
    if (sessionDead) throw new Error("Session dead");
    try {
      await useAuthStore().refresh();
    } catch (error) {
      sessionDead = true;
      useToasterStore().add({
        text: "Your session has expired, please log in again.",
        color: "error",
        timeout: 4000,
      });
      useAuthStore().logout(false);
      throw error;
    }
  },
  {
    shouldRefresh: (error) =>
      !sessionDead && !error.config?.url?.includes("/auth/"),
  },
);

export function resetSessionState() {
  sessionDead = false;
}

export default axios;
