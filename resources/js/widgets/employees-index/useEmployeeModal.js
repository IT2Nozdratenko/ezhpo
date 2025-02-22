import {computed, ref} from "vue";
import {usePageSetup} from "./usePageSetup";

export const useEmployeeModal = () => {
  const { rolesModalOptions, pointsModalOptions, clientRoleId, headOperatorSdpoRoleId } = usePageSetup()

  const isShow = ref(false)
  const employeeId = ref(null)

  const showModal = (id) => {
    isShow.value = true
    employeeId.value = id ?? null
  }

  const allPvs = computed(() => {
    return (pointsModalOptions.value ?? []).map((pvGroups) => {
      return (pvGroups.options ?? []).map((pv) => {
        return {
          id: pv.value,
          name: `${pvGroups.label} - ${pv.text}`
        }
      })
    }).flat()
  })

  return {
    isShow,
    employeeId,
    showModal,
    rolesModalOptions,
    clientRoleId,
    pointsModalOptions,
    headOperatorSdpoRoleId,
    allPvs,
  }
}