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
    default: false,
  },
  currentPage: {
    type: Number,
    required: true,
  },
  sortBy: {
    type: String,
    required: false,
    default: null,
  },
  sortDesc: {
    type: Boolean,
    default: false,
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

const emit = defineEmits([
  'update:sort-by',
  'update:sort-desc',
  'edit',
  'delete',
  'restore',
  'logs-read'
])

const displayedFields = computed(() => {
  const columns = props.fields.map((field) => {
    return {
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
    };
  });

  columns.push({key: 'buttons', label: '#', class: 'text-right'});

  if (props.isTrashMode) {
    columns.push({
      key: 'who_deleted',
      label: 'Имя удалившего',
    })
    columns.push({
      key: 'deleted_at',
      label: 'Время удаления',
    })
  }

  return columns
})

const handleSortUpdate = (e) => {
  emit('update:sort-by', e.sortBy)
  emit('update:sort-desc', e.sortDesc)
}

const handleEdit = (id) => {
  emit('edit', id)
}

const handleLogsRead = (id) => {
  emit('logs-read', id)
}

const handleDelete = (id) => {
  emit('delete', id)
}

const handleRestore = (id) => {
  emit('restore', id)
}
</script>

<template>
  <div class="card table-card">
    <div class="card-body pt-0">
      <b-table
        :fields="displayedFields"
        :items="props.items"
        :busy="props.busy"
        :sort-by="props.sortBy"
        :sort-desc="props.sortDesc"
        :current-page="props.currentPage"
        striped hover
        no-local-sorting
        @sort-changed="handleSortUpdate"
      >
        <template #cell(name)="row">
          <template v-if="props.canEdit">
            <a
              href="#"
              @click="handleEdit(row.item.id)"
            >
              {{ row.value }}
            </a>
          </template>
          <template v-else>
            {{ row.value }}
          </template>
        </template>

        <template #cell(pv)="row">
          {{ row.value }}
        </template>
        
        <template #cell(photo)="row">
          <img
            v-if="row.value"
            class="table-avatar"
            :src="'/storage/' + row.value"
            alt=""
          >
          <img
            v-else
            class="table-avatar"
            :src="'/img/default_profile.jpg'"
            alt=""
          >
        </template>
        <template #cell(company)="row">
          {{ row.value.name }}
        </template>
        <template #cell(blocked)="row">
          {{ row.value === 1 ? 'Да' : 'Нет' }}
        </template>
        <template #cell(roles)="row">
          <template v-for="role in row.value">
            <h5>
              <span class="badge badge-success">
                  {{ role }}
              </span>
            </h5>
          </template>
        </template>
        <template #cell(buttons)="row">
          <div class="d-flex">
            <b-button
              v-if="props.canReadLogs"
              size="sm"
              variant="primary"
              @click="handleLogsRead(row.item.user_id)"
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
              v-if="props.isTrashMode"
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
.table-avatar {
  width: 100px;
  height: 100px;
}

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
