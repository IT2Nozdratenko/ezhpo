import Swal2 from "sweetalert2";
import {deleteEmployee} from "./api";
import {ref} from "vue";

export const useDeleteEmployee = () => {
  const deleteEmployeePending = ref(false)

  const performDeleteEmployee = async (id) => {
    const result = await Swal2.fire({
      title: 'Вы уверены, что хотите удалить?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Да, удалить!',
      cancelButtonText: 'Отмена',
    });

    if (!result.isConfirmed) {
      return;
    }

    try {
      deleteEmployeePending.value = true;

      await deleteEmployee(id);
      await Swal2.fire('Удалено', 'Данные были успешно удалены', 'success');

      return Promise.resolve()
    } catch (e) {
      await Swal2.fire('Ошибка', e.message || 'Что-то пошло не так', 'warning');

      return Promise.reject()
    } finally {
      deleteEmployeePending.value = false;
    }
  };

  return {
    deleteEmployeePending,
    performDeleteEmployee,
  }
}