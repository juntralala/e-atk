export function canRequestItem(user) {
    return ['unit', 'administrator'].includes(user?.role?.name);
}

export function canReadReport(user) {
    return ['bendahara', 'petugas', 'administrator'].includes(user?.role?.name);
}

export function canSeeMasterData(user) {
    return ['administrator', 'bendahara', 'petugas'].includes(user?.role?.name);
}

export function canSetting(user) {
    return ['administrator'].includes(user?.role?.name);
}

export function canInUnitPage(user) {
    return ['bendahara', 'petugas', 'administrator'].includes(user?.role?.name);
}

export function canManageItem(user) {
    return ['bendahara', 'petugas', 'administrator'].includes(user?.role?.name);
}

export function canInUserPage(user) {
    return ['administrator', 'bendahara'].includes(user?.role?.name);
}

export function canAddItem(user) {
    return ['bendahara', 'petugas', 'administrator'].includes(user?.role?.name);
}

export function canInDashboard(user) {
    return ['bendahara', 'petugas', 'administrator'].includes(user?.role?.name);
}

export function canInItemRequestPage(user) {
    return ['bendahara', 'petugas', 'unit', 'administrator'].includes(user?.role?.name);
}

export function canDeleteItemRequest(user, itemRequest) {
    return itemRequest?.requester_id == user?.id;
}

export function canPrintItemRequest(user, itemRequest) {
    return itemRequest?.requester_id == user?.id;
}

export function canAcceptItemRequest(user) {
    return ['bendahara', 'petugas', 'administrator'].includes(user?.role?.name);
}

export function canRejectItemRequest(user) {
    return ['bendahara', 'petugas', 'administrator'].includes(user?.role?.name);
}