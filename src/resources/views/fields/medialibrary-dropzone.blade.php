@php
$url=$field['url']
@endphp

@include('crud::fields.inc.wrapper_start')
<div class="media-library-dropzone__dropzone dropzone" id="media-library-dropzone-dropzone"></div>
<div id="media-library-dropzone">
  <div class="media-library-dropzone__grid" id="media-library-dropzone-picture-list" ref="list">
    <div class="media-library-dropzone__item" v-for="picture in pictures" :key="picture.id" v-if="pictures.length > 0" :data-id="picture.id">
      <svg class="media-library-dropzone__close" @click="deletePicture(picture)" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
        <path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z"></path>
      </svg>
      <img v-bind:src="picture.url" class="media-library-dropzone__img" />
    </div>
  </div>
</div>
@include('crud::fields.inc.wrapper_end')

@if ($crud->fieldTypeNotLoaded($field))
  @php
  $crud->markFieldTypeAsLoaded($field);
  @endphp
  @push('crud_fields_styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/dropzone.min.css" integrity="sha512-3g+prZHHfmnvE1HBLwUnVuunaPOob7dpksI7/v6UnF/rnKGwHf/GdEq9K7iEN7qTtW+S0iivTcGpeTBqqB04wA==" crossorigin="anonymous" />
    <style>
      .media-library-dropzone__dropzone {
        width: 100%;
        border-radius: 0.25rem;
      }

      .media-library-dropzone__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        grid-gap: 1.25rem;
        margin-top: 1.25rem;
        margin-bottom: 1.25rem;
        justify-content: center;
      }

      .media-library-dropzone__item {
        position: relative;
      }

      .media-library-dropzone__close {
        position: absolute;
        right: 0;
        top: 0;
        margin-right: 0.5rem;
        margin-top: 0.5rem;
        cursor: pointer;
        width: 2rem;
        color: #fff;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        height: 2rem;
        transition: color 100ms ease-in-out;
      }

      .media-library-dropzone__close:hover {
        color: #ccc;
      }

      .media-library-dropzone__img {
        border-radius: 0.25rem;
        max-width: 100%
      }

    </style>
  @endpush


  @push('crud_fields_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/dropzone.min.js" integrity="sha512-8l10HpXwk93V4i9Sm38Y1F3H4KJlarwdLndY9S5v+hSAODWMx3QcAVECA23NTMKPtDOi53VFfhIuSsBjjfNGnA==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.11/vue.min.js" integrity="sha512-QJsj3afWlCpnQ60XzzYE8Jb3IgHIK1BcGZox9rUSmRRq3Ocz9vQ1yR80eX2/b9jfx/UuT5zYWNdrM8Te/eCMhQ==" crossorigin="anonymous"></script>
    <script src="https://raw.githack.com/SortableJS/Sortable/master/Sortable.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script charset="utf-8">
      Dropzone.autoDiscover = false;
      new Dropzone("#media-library-dropzone-dropzone", {
        'headers': {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        init: function() {
          this.on("complete", function() {
            window.mediaLibDropzone.getPictures();
          });
        },
        url: '{{ $url }}'
      });
      window.mediaLibDropzone = new Vue({
        el: '#media-library-dropzone',
        data: {
          pictures: [],
          url: '{{ $url }}'
        },
        created: function() {
          this.getPictures();
        },
        methods: {
          getPictures: function() {
            axios.get(this.url).then(res => {
              this.pictures = res.data;
              this.createSortable();
            });
          },
          createSortable: function() {
            Sortable.create(this.$refs.list, {
              animation: 100,
              onSort: (el) => {
                var index = 0;
                var newOrder = Array.from(document.getElementById('media-library-dropzone-picture-list').children).map(el => {
                  return {
                    id: el.getAttribute('data-id'),
                    order: index++
                  };
                });
                axios.post(this.url + "-sort", {
                  pictures: newOrder
                });
              }
            });
          },
          deletePicture: function(picture) {
            this.pictures = this.pictures.filter(x => x !== picture);
            axios.delete(`${this.url}/${picture.id}`).then(() => this.getPictures());
          },
        }
      });

    </script>
  @endpush
@endif
