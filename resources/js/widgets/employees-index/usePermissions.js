import {usePageSetup} from "./usePageSetup";
import {computed} from "vue";

export const usePermissions = () => {
  const { currentUserPermissions } = usePageSetup()

  const can = computed(() => ({
    create: !!currentUserPermissions.value.permission_to_create,
    edit: !!currentUserPermissions.value.permission_to_edit,
    trash: !!currentUserPermissions.value.permission_to_trash,
    view: !!currentUserPermissions.value.permission_to_view,
    logsRead: !!currentUserPermissions.value.permission_to_logs_read,
    delete: !!currentUserPermissions.value.permission_to_delete,
  }))

  return {
    can,
  }
}