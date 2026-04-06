import type { App } from "vue";
import { createPinia } from "pinia";
import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import router from "../router";
import vuetify from "./vuetify";

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

export function registerPlugins(app: App) {
  app.use(vuetify);
  app.use(pinia);
  app.use(router);
}
