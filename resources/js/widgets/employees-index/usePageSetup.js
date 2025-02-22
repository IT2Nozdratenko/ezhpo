import {reactive, ref} from "vue";

const toNumber = (ids) => {
    if (Array.isArray(ids)) {
        return ids.map((id) => +id)
    }

    return +ids
}

export const usePageSetup = () => {
    const fields = ref(window.PAGE_SETUP.fields)
    const isTrashMode = ref(!!window.PAGE_SETUP.isTrashMode)
    const currentUserPermissions = ref(window.PAGE_SETUP.currentUserPermissions)

    // modal
    const rolesModalOptions = ref(window.PAGE_SETUP.rolesModalOptions)
    const pointsModalOptions = ref(window.PAGE_SETUP.pointsModalOptions)
    const allPermissions = ref(window.PAGE_SETUP.allPermissions)
    const clientRoleId = ref(window.PAGE_SETUP.clientRoleId)
    const headOperatorSdpoRoleId = ref(window.PAGE_SETUP.headOperatorSdpoRoleId)

    // filter
    const rolesFilterOptions = ref(window.PAGE_SETUP.rolesFilterOptions)
    const pointsFilterOptions = ref(window.PAGE_SETUP.pointsFilterOptions)
    const employeesFilterOptions = ref(window.PAGE_SETUP.employeesFilterOptions)

    const filterValues = reactive({
        employeeIds: toNumber(window.PAGE_SETUP.selectedEmployeeIds),
        pointIds: toNumber(window.PAGE_SETUP.selectedPointIds),
        roleId: toNumber(window.PAGE_SETUP.selectedRoleId),
        email: window.PAGE_SETUP.selectedEmail,
    })

    return {
        fields,
        isTrashMode,
        currentUserPermissions,

        rolesModalOptions,
        pointsModalOptions,
        allPermissions,
        clientRoleId,
        headOperatorSdpoRoleId,

        rolesFilterOptions,
        pointsFilterOptions,
        employeesFilterOptions,

        filterValues,
    }
}
