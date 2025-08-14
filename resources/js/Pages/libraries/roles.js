export const roleLabels = {
    1: "SuperAdmin",
    2: "Admin",
    3: "Supervisor",
    4: "Cashier",
};

export const roleOptions = [
    { value: 1, label: "SuperAdmin" },
    { value: 2, label: "Admin" },
    { value: 3, label: "Supervisor" },
    { value: 4, label: "Cashier" },
];

export const canDelete = (authUser, targetAccount) => {
    if (!authUser || !targetAccount) return false;

    const myRole = parseInt(authUser.acct_roles ?? 0);
    const targetRole = parseInt(targetAccount.acct_roles ?? 0);

    if (myRole === 1) return true;
    if (myRole === 2 && targetRole !== 1) return true;
    if (myRole === 3 && targetRole === 4) return true;

    return false;
};

export const getAssignableRoles = (authUser) => {
    const myRole = parseInt(authUser.acct_roles ?? 0);

    if (myRole === 1) {
        return roleOptions; // All
    }
    if (myRole === 2) {
        return roleOptions.filter(r => r.value !== 1); // No SuperAdmin
    }
    if (myRole === 3) {
        return roleOptions.filter(r => r.value === 4); // Only Cashier
    }
    return [];
};
export const StatusOptions = [
    { value: 1, label: "Active" },
    { value: 2, label: "Deactive" },
];