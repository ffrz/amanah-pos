export function can(permissionName, page) {
  const user = page?.props?.auth?.user;
  const permissions = window.CONSTANTS.PERMISSIONS;

  if (user?.type == "super_user") return true;

  return permissions.includes(permissionName);
}
