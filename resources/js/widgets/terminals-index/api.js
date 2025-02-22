export const fetchTerminalTableItems = async (params) => {
  return await axios.get('/terminals/v2/table-items' + window.location.search, {
    params: {
      sortBy: params.sortBy,
      sortDesc: params.sortDesc,
      page: params.page,
      take: params.perPage,
    }
  })
}

export const fetchTerminalItem = async (id) => {
  return await axios.get(`/terminals/v2/${id}`)
}

export const createTerminal = async (params) => {
  return await axios.post(`/terminals/v2`, params)
}

export const updateTerminal = async (id, params) => {
  return await axios.put(`/terminals/v2/${id}`, params)
}

export const deleteTerminal = async (id) => {
  return await axios.delete(`/terminals/v2/${id}`)
}

export const fetchConnectionStatus = async (ids) => {
  return await axios.post('/terminals/v2/status', {
    terminals_ids: ids
  })
}

export const fetchTerminalsToCheck = async () => {
  return await axios.get('/terminals/v2/to-check')
}