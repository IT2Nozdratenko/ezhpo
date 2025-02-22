<script setup>
import {computed} from "vue";

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  fields: {
    type: Array,
    required: true,
  },
  busy: {
    type: Boolean,
    required: true,
  },
  sortBy: {
    type: String,
    required: false,
    default: null,
  },
  sortDesc: {
    type: Boolean,
    required: true,
  },
  page: {
    type: Number,
    required: true,
  },
  canView: {
    type: Boolean,
    required: true,
  },
  canEdit: {
    type: Boolean,
    required: true,
  },
  canReadLogs: {
    type: Boolean,
    required: true,
  },
  canDelete: {
    type: Boolean,
    required: true,
  },
  canRestore: {
    type: Boolean,
    required: true,
  },
  isTrashMode: {
    type: Boolean,
    required: true,
  }
})

const emit = defineEmits(['edit', 'delete', 'restore', 'read-logs', 'update:sort-by', 'update:sort-desc'])

const sortChanged = (e) => {
  emit('update:sort-by', e.sortBy)
  emit('update:sort-desc', e.sortDesc)
  console.log(e.sortBy, e.sortDesc)
}

const tableRowClass = (item, type) => {
  if (item && type === 'row') {
    if (item?.need_check?.in_a_month) {
      return 'row-check-in-a-month'
    } else if (item?.need_check?.expired) {
      return 'row-check-expired'
    } else {
      return ''
    }
  } else {
    return null
  }
}

const displayedFields = computed(() => {
  const res = []

  props.fields.forEach(field => {
    res.push({
      'key': field.field,
      'label': field.name,
      'sortable': true,
      'thAttr': {
        'data-toggle': 'tooltip',
        'data-html': true,
        'data-trigger': 'hover',
        'data-placement': 'top',
        title: field.content,
      }
    });
  });

  res.push({key: 'buttons', label: '#', class: 'text-right'});

  if (props.isTrashMode) {
    res.push(...[
      {
        key: 'who_deleted',
        label: 'Имя удалившего',
      },
      {
        key: 'deleted_at',
        label: 'Время удаления',
      }
    ])
  }

  return res
})

const formatDate = (date) => {
  const dateObject = new Date(date)
  return `${String(dateObject.getDate()).padStart(2, '0')}.${String(dateObject.getMonth() + 1).padStart(2, '0')}.${dateObject.getFullYear()}`
}

const handleEdit = (id) => {
  emit('edit', id)
}

const handleDelete = (id) => {
  emit('delete', id)
}

const handleRestore = (id) => {
  emit('restore', id)
}

const handleReadLogs = (id) => {
  emit('read-logs', id)
}
</script>

<template>
  <div class="card table-card">
    <div class="card-body">
      <b-table
        v-if="props.canView"
        :items="props.items"
        :fields="displayedFields"
        :busy="props.busy"
        :sort-by="props.sortBy"
        :sort-desc="props.sortDesc"
        :current-page="props.page"
        :tbody-tr-class="tableRowClass"
        striped hover
        no-local-sorting
        @sort-changed="sortChanged"
      >
        <template #cell(status)="{ item }">
          <span v-if="item.connected" class="badge badge-success">on</span>
          <span v-else class="badge badge-danger">off</span>
        </template>

        <template #cell(date_end_check)="{ item }">
          {{ item.date_end_check ? formatDate(item.date_end_check) : '' }}
        </template>

        <template #cell(name)="row">
          <template v-if="props.canEdit">
            <a href="#" @click="handleEdit(row.item.id)">
              {{ row.value || 'Неизвестно' }}
            </a>
          </template>
          <template v-else>
            {{ row.value || 'Неизвестно' }}
          </template>
        </template>

        <template #cell(serial_number)="{ item }">
          {{ item.serial_number ?? '' }}
        </template>

        <template #cell(company_id)="{ item }">
          {{ item?.company_id ?? '' }}
        </template>

        <template #cell(stamp_id)="{ item }">
          {{ item.stamp_id ?? 'Неизвестно' }}
        </template>

        <template #cell(town)="{ item }">
          {{ item.town ?? 'Неизвестно' }}
        </template>

        <template #cell(pv)="{ item }">
          {{ item.pv ?? '' }}
        </template>

        <template #cell(timezone)="{ item }">
          {{ item.timezone ?? '' }}
        </template>

        <template #cell(blocked)="{ item }">
          {{ item.blocked === 1 ? 'Да' : 'Нет' }}
        </template>

        <template #cell(api_token)="{ item }">
          {{ item.api_token ?? '' }}
        </template>

        <template #cell(buttons)="row">
          <div class="d-flex">
            <b-button
              v-if="props.canReadLogs"
              size="sm"
              variant="primary"
              @click="handleReadLogs(row.item.id)"
              title="Журнал действий"
            >
              <i class="fa fa-book"></i>
            </b-button>
            <b-button
              v-if="!props.isTrashMode"
              :disabled="!props.canDelete"
              variant="danger"
              size="sm"
              class="ml-1"
              @click="handleDelete(row.item.id)">
              <i class="fa fa-trash"></i>
            </b-button>
            <b-button
              v-if="isTrashMode"
              :disabled="!props.canRestore"
              variant="warning"
              size="sm"
              class="ml-1"
              @click="handleRestore(row.item.id)">
              <i class="fa fa-undo"></i>
            </b-button>
          </div>
        </template>
      </b-table>
    </div>
  </div>
</template>

<style scoped lang="scss">
.table-card {
  max-height: 80vh;
  overflow: hidden;
}

.table-card > .card-body {
  overflow: scroll;
  padding: 0 !important;
  margin: 15px !important;
  overscroll-behavior: contain;
}
</style>