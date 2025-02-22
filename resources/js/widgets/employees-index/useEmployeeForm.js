import {computed, reactive, ref, watch} from "vue";
import {fetchEmployee, fetchPermissionsByRoles} from "./api";
import {usePageSetup} from "./usePageSetup";
import {debounce} from "lodash";

export const useEmployeeForm = () => {
  const {allPermissions} = usePageSetup()

  const fetchFormPending = ref(false)

  const form = reactive({
    name: null,
    login: null,
    email: null,
    password: null,
    eds: null,
    validityEdsStart: null,
    validityEdsEnd: null,
    timezone: null,
    roles: [],
    blocked: 0,
    permissionIds: [],
    pv: null,
    pvs: [],
  })
  const permissionTabExpanded = ref(false)
  const searchPermissions = ref('')
  const permissionsByRoles = ref([])

  const searchedPermissions = computed(() => {
    if (!searchPermissions) {
      return allPermissions.value
    }

    return allPermissions.value.filter((item) => {
      return item.guard_name.toLowerCase().includes(searchPermissions.value.toLowerCase())
    })
  })

  const displayedPermissions = computed(() => {
    return searchedPermissions.value.map((item) => {
      return {
        id: item.id,
        text: item.guard_name,
        disabled: permissionsByRoles.value.includes(item.id),
      }
    })
  })

  const fetchForm = async (employeeId) => {
    fetchFormPending.value = true

    try {
      const {data} = await fetchEmployee(employeeId)

      form.id = data.id;
      form.name = data.name
      form.login = data.login
      form.email = data.email
      form.eds = data.eds
      form.timezone = data.timezone
      form.pv = data.pv_id
      form.pvs = data.pv_ids
      form.roles = data.roles.map((item) => ({
        id: item.id,
        text: item.guard_name,
      }))
      form.blocked = data.blocked
      form.validityEdsStart = data.validity_eds_start
      form.validityEdsEnd = data.validity_eds_end

      form.permissionIds = data.permission_ids
    } catch (e) {
      console.log(e)
    } finally {
      fetchFormPending.value = false
    }
  }

  const fetchPermissions = async () => {
    const unassignedPermissionIds = form.permissionIds.filter((item) => {
      return !permissionsByRoles.value.includes(item)
    })

    const roleIds = form.roles.map((item) => item.id)
    const {data} = await fetchPermissionsByRoles(roleIds)

    permissionsByRoles.value = data
    form.permissionIds = [...unassignedPermissionIds, ...data]
  }

  const makeParams = () => {
    return {
      name: form.name,
      login: form.login,
      email: form.email,
      eds: form.eds,
      timezone: form.timezone,
      password: form.password,
      pv_id: form.pv ?? null,
      pvs: form.pvs,
      roles: form.roles.map((item) => {
        return item.id;
      }),
      blocked: form.blocked,
      validity_eds_start: form.validityEdsStart ?? null,
      validity_eds_end: form.validityEdsEnd ?? null,
      permissions: form.permissionIds.filter((item) => {
        return !permissionsByRoles.value.includes(item)
      }),
    }
  }

  const resetForm = () => {
    form.name = null
    form.login = null
    form.email = null
    form.password = null
    form.eds = null
    form.validityEdsStart = null
    form.validityEdsEnd = null
    form.timezone = null
    form.roles = []
    form.permissionIds = []
    form.blocked = 0
    form.pv = null
    form.pvs = []

    permissionTabExpanded.value = false
    searchPermissions.value = ''
    permissionsByRoles.value = []
  }

  watch(
    () => form.roles,
    debounce(async () => {
      await fetchPermissions()
    }, 200)
  )

  return {
    fetchFormPending,
    form,
    permissionTabExpanded,
    searchPermissions,
    displayedPermissions,
    fetchForm,
    fetchPermissions,
    makeParams,
    resetForm,
  }
}