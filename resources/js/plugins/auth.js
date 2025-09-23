export function can(permissionName, page) {
  const user = page?.props?.auth?.user;
  const permissions = window.CONSTANTS.PERMISSIONS;

  if (user?.role == "admin") return true;

  return permissions.includes(permissionName);
}
