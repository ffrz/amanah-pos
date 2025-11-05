import { computed } from "vue";

/**
 * Hook composable untuk memproses error Inertia, mengubahnya dari array/object
 * menjadi format yang lebih mudah digunakan, terutama untuk Quasar inputs.
 *
 * @param {import("vue").Ref<Object> | Object} formErrors - Objek error dari Inertia form.
 * @param {import("vue").Ref<Boolean> | Boolean} isDialogMode - Apakah form berjalan dalam mode dialog (default: false).
 * @returns {{ processedErrors: import("vue").ComputedRef<Object>, getErrorMessage: (key: string) => string | null }}
 */
export function useFormError(formErrors, isDialogMode = false) {
  const errorsRef = computed(() =>
    typeof formErrors === "function" ? formErrors() : formErrors
  );
  const dialogModeRef = computed(() =>
    typeof isDialogMode === "function" ? isDialogMode() : isDialogMode
  );

  const processedErrors = computed(() => {
    const finalErrors = {};

    if (dialogModeRef.value) {
      for (const key in errorsRef.value) {
        const errorValue = errorsRef.value[key];
        if (Array.isArray(errorValue) && errorValue.length > 0) {
          finalErrors[key] = errorValue[0];
        } else if (typeof errorValue === "string") {
          finalErrors[key] = errorValue;
        }
      }
    } else {
      return errorsRef.value;
    }
    return finalErrors;
  });

  const getErrorMessage = (key) => {
    return processedErrors.value[key] || null;
  };

  return {
    processedErrors,
    getErrorMessage,
  };
}
