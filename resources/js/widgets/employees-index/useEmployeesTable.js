import {reactive, watch} from "vue";
import {debounce} from "lodash";
import {fetchEmployeesTableItems} from "./api";
import {ref} from "vue";

export const useEmployeesTable = () => {
  const fetchTablePending = ref(false)

  const table = reactive({
    items: [],
    total: 0,
    page: 1,
    perPage: 15,
  })

  const params = reactive({
    sortBy: null,
    sortDesc: null,
  })

  const fetchEmployeesItems = async () => {
    fetchTablePending.value = true

    try {
      const { data } = await fetchEmployeesTableItems({
        sortBy: params.sortBy,
        sortDesc: params.sortDesc,
        page: table.page,
        perPage: table.perPage,
      })

      table.items = data.items;
      table.page = data.page;
      table.total = data.total;
    } catch (e) {
      console.log(e)
    } finally {
      fetchTablePending.value = false
    }
  }

  watch([
    () => table.page, () => params.sortBy, () => params.sortDesc],
    debounce(async () => {
      await fetchEmployeesItems()
    }, 200)
  )

  return {
    fetchTablePending,
    table,
    params,
    fetchEmployeesItems
  }
}