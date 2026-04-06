import type { AxiosError } from "axios";
import type { Ref } from "vue";

export function useApiErrorHandler() {
  function handle(error: unknown, errors?: Ref<Record<string, string[]>>) {
    const axiosError = error as AxiosError<{
      message: string;
      errors: Record<string, string[]> | null;
    }>;

    if (axiosError.response?.status === 422 && errors) {
      errors.value = axiosError.response.data.errors ?? {};
    }
  }

  return { handle };
}
