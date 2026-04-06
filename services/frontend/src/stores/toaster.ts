import type { Toast } from "@/types/Toast";
import { defineStore } from "pinia";

export const useToasterStore = defineStore("toaster", {
  state: () => ({
    queue: [] as Toast[],
  }),

  actions: {
    add(toast: Toast) {
      this.queue.push(toast);
    },
  },
});
