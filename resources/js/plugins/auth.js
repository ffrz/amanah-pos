export function can(permissionName, page) {
  const user = page?.props?.auth?.user;
  const permissions = page?.props?.auth?.permissions || [];

  if (user?.is_root) return true;

  return permissions.includes(permissionName);
}
