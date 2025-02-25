<script>
import swal from "sweetalert2";

export default {
  name: "attach-photos-index",
  components: {},
  props: {
    items: {
      type: Array,
      required: false,
    },
    id: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      photos: [],
    }
  },
  methods: {
    changePhoto(fileInput) {
      let newPhotos = Array.from(fileInput.target.files)

      if (newPhotos.length) {
        const uniqueFiles = new Map()

        this.photos.forEach(file => {
          const key = `${file.name}-${file.size}`
          if (!uniqueFiles.has(key)) {
            uniqueFiles.set(key, file);
          }
        });

        newPhotos.forEach(file => {
          const key = `${file.name}-${file.size}`
          if (!uniqueFiles.has(key)) {
            uniqueFiles.set(key, file)
          }
        })

        newPhotos = Array.from(uniqueFiles.values())

        if (this.items.length + newPhotos.length > 4) {
          swal.fire({
            title: 'Ошибка',
            text: 'Максимальное количество файлов: 4',
            icon: 'error'
          })

          return
        }
        this.photos = newPhotos

        const dataTransfer = new DataTransfer()

        this.photos.forEach(file => {
          dataTransfer.items.add(file)
        })

        const fileInput = $('.custom-file-input')[0]
        fileInput.files = dataTransfer.files
      }
    },
    removeOld(path) {
      const url = '/trip-tickets/'+this.id+'/delete-photo'

      axios
          .post(url, {
            path
          })
          .then(() => {
            location.reload()
          })
          .catch(error => {
            console.log(error.response.data)
            swal.fire({
              title: 'Ошибка',
              text: 'Ошибка удаления файла',
              icon: 'error'
            })
          })
    },
    removeNew(index) {
      this.photos.splice(index, 1)
    }
  },
  async mounted() {
  },
  computed: {
    enableSaveBtn() {
      return this.photos.length === 0
    }
  }
}
</script>

<template>
  <div>
    <div class="col-12 text-center mt-3">
      <b>Загрузка фото:</b>
    </div>
    <div class="input-group text-left mt-1 mb-3">
      <div class="custom-file">
        <input
            type="file"
            class="custom-file-input"
            id="photo"
            accept="image/jpeg, image/png, application/pdf"
            name="photos[]"
            multiple
            @change="changePhoto"
        >
        <label class="custom-file-label mr-0" for="photo">Выберите изображение:</label>
      </div>
    </div>

    <div v-if="this.items.length" id="preview" class="d-flex flex-column mt-3">
      <p><b>Загруженные на сервер фото:</b></p>

      <div v-for="(photo, index) in this.items" class="row photo-item-div text-left">
        <div class="col-md-12 input-group d-flex justify-content-between align-items-center">
          <a class="form-control truncate-text" :href="photo.url" target="_blank">{{ photo.original_name }}</a>

          <div class="input-group-append">
            <button
                class="btn btn-outline-danger"
                type="button"
                :id="'remove-photo-'+index"
                @click="removeOld(photo.path)"
            >
              <i class="fa fa-times"></i></button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="this.photos.length" id="preview" class="d-flex flex-column mt-3">
      <p><b>Фото для загрузки:</b></p>

      <div v-for="(photo, index) in this.photos" class="row photo-item-div text-left">
        <div class="col-md-12 input-group">
          <input type="text" class="form-control truncate-text" :value="photo.name" :title="photo.name" :aria-describedby="'remove-photo-'+index" disabled>
          <div class="input-group-append">
            <button
                class="btn btn-outline-danger"
                type="button"
                :id="'remove-photo-'+index"
                @click="removeNew(index)"
            >
              <i class="fa fa-times"></i></button>
          </div>
        </div>
      </div>
    </div>

    <div class="form-group row mb-0 mt-3 d-flex justify-content-center">
      <a href="/" class="btn btn-sm btn-info">Главная</a>
      <button type="submit" class="btn btn-sm btn-success submit-btn ml-2" :disabled="enableSaveBtn">Сохранить
      </button>
    </div>
  </div>
</template>

<style scoped>
.photo-item-div + .photo-item-div {
  margin-top: 0.75rem;
}

.form-control {
  height: calc(1.5em + .75rem + 2px);
  padding: .375rem .75rem;
  font-size: 1rem;
  font-weight: 400;
  line-height: 1.5;
  color: #495057;
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: .25rem;
}

.truncate-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
