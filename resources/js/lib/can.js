const ROLES = {
  ADMIN_ONLY: new Set(['administrator']),
  UNIT_ADMIN: new Set(['unit', 'administrator']),
  PETUGAS_ADMIN: new Set(['petugas', 'administrator']),
  BENDAHARA_ADMIN: new Set(['administrator', 'bendahara']),
  BENDAHARA_PETUGAS_ADMIN: new Set(['bendahara', 'petugas', 'administrator']),
  PETUGAS_UNIT_ADMIN: new Set(['petugas', 'unit', 'administrator']),
};

function hasRole(user, roleSet) {
  return roleSet.has(user?.role?.name);
}

// Permissions
export function canRequestItem(user) {
  return hasRole(user, ROLES.UNIT_ADMIN);
}

export function canReadReport(user) {
  return hasRole(user, ROLES.BENDAHARA_PETUGAS_ADMIN);
}

export function canAccessItemReport(user) {
  return hasRole(user, ROLES.PETUGAS_ADMIN);
}

export function canAccessItemRequestReport(user) {
  return hasRole(user, ROLES.PETUGAS_ADMIN);
}

export function canAccessItemAdditionReport(user) {
  return hasRole(user, ROLES.PETUGAS_ADMIN);
}

export function canAccessItemExpenditureReport(user) {
  return hasRole(user, ROLES.BENDAHARA_PETUGAS_ADMIN);
}

export function canAccessUnitExpenditureReport(user) {
  return hasRole(user, ROLES.BENDAHARA_PETUGAS_ADMIN);
}

export function canSeeMasterData(user) {
  return hasRole(user, ROLES.PETUGAS_ADMIN);
}

export function canSetting(user) {
  return hasRole(user, ROLES.ADMIN_ONLY);
}

export function canInUnitPage(user) {
  return hasRole(user, ROLES.PETUGAS_ADMIN);
}

export function canManageItem(user) {
  return hasRole(user, ROLES.PETUGAS_ADMIN);
}

export function canInUserPage(user) {
  return hasRole(user, ROLES.BENDAHARA_ADMIN);
}

export function canAddItem(user) {
  return hasRole(user, ROLES.PETUGAS_ADMIN);
}

export function canInDashboard(user) {
  return hasRole(user, ROLES.BENDAHARA_PETUGAS_ADMIN);
}

export function canInItemRequestPage(user) {
  return hasRole(user, ROLES.PETUGAS_UNIT_ADMIN);
}

export function canDeleteItemRequest(user, itemRequest) {
  return itemRequest?.requester_id == user?.id;
}

export function canPrintItemRequest(user, itemRequest) {
  return itemRequest?.requester_id == user?.id;
}

export function canAcceptItemRequest(user) {
  return hasRole(user, ROLES.PETUGAS_ADMIN);
}

export function canRejectItemRequest(user) {
  return hasRole(user, ROLES.PETUGAS_ADMIN);
}

export function canInItemListPage(user) {
  return hasRole(user, ROLES.UNIT_ADMIN);
}

export function canActItem(user) {
  return hasRole(user, ROLES.PETUGAS_ADMIN);
}