export const fetchEmployeesTableItems = async (params) => {
  return await axios.get('/employees/table-items' + window.location.search, {
    params: {
      sortBy: params.sortBy,
      sortDesc: params.sortDesc,
      page: params.page,
      take: params.perPage,
    },
  })
}

export const fetchEmployee = async (id) => {
  return await axios.get(`/employees/${id}`)
}

export const deleteEmployee = async (id) => {
  return await axios.delete(`/employees/${id}`)
}

export const restoreEmployee = async (id) => {
  return await axios.post(`/employees/${id}/restore`)
}

export const fetchPermissionsByRoles = async (roleIds) => {
  return await axios.post('/employees/permissions-by-roles', {
    role_ids: roleIds
  })
}

export const createEmployee = async (params) => {
  return await axios.post('/employees', params)
}

export const updateEmployee = async (id, params) => {
  return await axios.put(`/employees/${id}`, params)
}