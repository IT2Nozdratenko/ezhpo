import Swal2 from "sweetalert2";
import {restoreEmployee} from "./api";
import {ref} from "vue";

export const useRestoreEmployee = () => {
  const restoreEmployeePending = ref(false)

  const performRestoreEmployee = async (id) => {
    try {
      restoreEmployeePending.value = true;

      await restoreEmployee(id);
      await Swal2.fire('Восстановлено', 'Данные были успешно восстановлены', 'success');

      return Promise.resolve()
    } catch (e) {
      await Swal2.fire('Ошибка', e.message, 'warning');

      return Promise.reject()
    } finally {
      restoreEmployeePending.value = false;
    }
  };

  return {
    restoreEmployeePending,
    performRestoreEmployee,
  }
}