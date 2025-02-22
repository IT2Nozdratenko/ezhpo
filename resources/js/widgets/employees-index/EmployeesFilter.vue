<script setup>
import {usePageSetup} from "./usePageSetup";

const {
  filterValues,
  isTrashMode,
  rolesFilterOptions,
  pointsFilterOptions,
  employeesFilterOptions,
} = usePageSetup()
</script>

<template>
  <div class="card mb-3">
    <div class="card-body">
      <form action="" method="GET">
        <input v-if="isTrashMode" type="hidden" value="1" name="deleted">

        <div class="row">
          <div class="col-lg-3 form-group">
            <select class="form-control" name="role" style="color: gray;">
              <option value="" selected>Роль</option>
              <option
                v-for="item of rolesFilterOptions"
                :key="item.id"
                :value="item.id"
                :selected="filterValues.roleId === item.id"
              >
                {{ item.text }}
              </option>
            </select>
          </div>

          <div class="col-lg-3 form-group">
            <input
              type="text"
              name="email"
              :value="filterValues.email"
              placeholder="E-mail"
              class="form-control"
            >
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6">
            <select
              multiple
              name="employee_id[]"
              data-label="id"
              data-field="Employees_id"
              data-allow-clear="true"
              data-placeholder="Сотрудник"
              class="filled-select2 filled-select select2-hidden-accessible"
              aria-hidden="true">
              <option></option>
              <option
                v-for="item of employeesFilterOptions"
                :key="item.id"
                :value="item.id"
                :selected="filterValues.employeeIds.includes(item.id)"
              >
                {{ item.text }}
              </option>
            </select>
          </div>

          <div class="col-lg-6 form-group">
            <select
              multiple
              name="point_id[]"
              data-label="id"
              data-field="Points_id"
              data-allow-clear="true"
              data-placeholder="ПВ"
              class="filled-select2 filled-select select2-hidden-accessible"
              aria-hidden="true"
            >
              <option></option>
              <option
                v-for="item of pointsFilterOptions"
                :key="item.id"
                :value="item.id"
                :selected="filterValues.pointIds.includes(item.id)"
              >
                {{ item.text }}
              </option>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-3 form-group">
            <input type="submit" class="btn btn-success btn-sm" value="Поиск">
            <a href="/employees" class="btn btn-danger btn-sm">Сбросить</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped></style>