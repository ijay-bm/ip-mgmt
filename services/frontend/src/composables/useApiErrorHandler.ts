import type { AxiosError } from "axios";
import type { Ref } from "vue";
import { useToasterStore } from "../stores/toaster";

export function useApiErrorHandler() {
  function handle(error: unknown, errors?: Ref<Record<string, string[]>>) {
    const axiosError = error as AxiosError<{
      message: string;
      errors: Record<string, string[]> | null;
    }>;

    if (axiosError.response?.status === 422 && errors) {
      errors.value = axiosError.response.data.errors ?? {};
    }

    if (
      axiosError.response?.status !== 422 &&
      axiosError.response?.status !== 401
    ) {
      useToasterStore().add({
        text: axiosError.response?.data.message ?? "Something went wrong",
        color: "error",
      });
    }
  }

  return { handle };
}
