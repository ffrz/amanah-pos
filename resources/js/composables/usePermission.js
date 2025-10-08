import { getCurrentInstance } from "vue";

export function useCan() {
  const instance = getCurrentInstance();
  if (!instance) {
    throw new Error("useCan must be called within a component setup function.");
  }
  return (permissionName) => instance.proxy.$can(permissionName);
}
