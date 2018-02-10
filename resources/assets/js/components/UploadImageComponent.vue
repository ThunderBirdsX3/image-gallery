<template>
    <div>
        <div class="upload-image" v-bind:class="{ 'dragover': onDragover }">
            <div class="form" id="upload_image_form">
                <input type="file" id="upload_image_form_input" multiple accept="image/*" />
            </div>
        </div>

        <div class="upload-image-thumbnails">
            <div v-for="(image, index) in images" class="upload-image-thumbnail" v-bind:class="{ 'uploaded': image.src != null }">

                <div class="btn-group" v-if="image.src != null">
                    <a v-bind:href="url + '/' + image.src" v-bind:data-lightbox="image.src" class="btn btn-primary btn-sm">
                        <i class="material-icons">zoom_in</i>
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" v-on:click="removeImage(image.id, index)">
                        <i class="material-icons">delete</i>
                    </button>
                </div>
                <div class="btn-group" v-else>
                    <button type="button" class="btn btn-danger btn-sm" v-on:click="removeImage(null, index)">
                        <i class="material-icons">delete</i>
                    </button>
                </div>


                <div v-if="image.src != null">
                    <img v-bind:src="url + '/' + image.src" class="show">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['galleries'],
        data: function(){
            return {
                url: route('gallery.index'),
                form: null,
                input: null,
                max_filesize: 10000,
                index: this.galleries.length,
                images: this.galleries,
                onDragover: false,
                onUploading: false
            }
        },
        mounted: function(){
            this.form = document.getElementById('upload_image_form');
            this.input = document.getElementById('upload_image_form_input');

            ['drag', 'dragstart', 'dragend','dragover', 'dragenter', 'dragleave', 'drop']
            .forEach(event => this.form.addEventListener(event, (e) => {
                e.preventDefault(); e.stopPropagation();
            }));

            ['dragover', 'dragenter']
            .forEach(event => this.form.addEventListener(event, this.dragEnter));

            ['dragleave', 'dragend', 'drop']
            .forEach(event => this.form.addEventListener(event, this.dragLeave));

            ['drop']
            .forEach(event => this.form.addEventListener(event, this.fileDrop));

            ['change']
            .forEach(event => this.input.addEventListener(event, this.fileDrop));

            this.form.addEventListener('click', (e) => {
                this.input.click();
            });
        },
        methods: {
            _xhr: function(file, index){

                let image = new FormData();
                image.append('image', file);

                axios.post(route('gallery.store'), image, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: function( progressEvent ) {
                        // this.uploadPercentage = parseInt( Math.round( ( progressEvent.loaded * 100 ) / progressEvent.total ) );
                    }.bind(this)
                })
                .then((response) => {

                    this.images[index]['src'] = response.data.src;
                    this.images[index]['uploaded'] = true;
                    console.log(this.images);

                })
                .catch((error) => {
                    console.error(error);
                });
            },

            dragEnter: function(e){
                e.preventDefault();
                this.onDragover = true;
            },
            dragLeave: function(e){
                e.preventDefault();
                this.onDragover = false;
            },
            fileDrop: function(e){
                e.preventDefault();

                let files = e.target.files || e.dataTransfer.files;

                for (let i = 0; i < files.length; i++) {

                    Vue.set(this.images, this.index, {
                        'bad_size': false,
                        'bad_type': false,
                        'uploaded': false,
                        'src': null,
                        'percent': false,
                    })

                    if((files[i].size * 0.001) > this.max_filesize) {
                        this.images[this.index]['bad_size'] = true;
                        console.log('bad_size');
                    } else if (! /\.(jpe?g|png)$/i.test( files[i].name ) ) {
                        this.images[this.index]['bad_type'] = true;
                        console.log('bad_type');
                    } else {
                        this._xhr(files[i], this.index);
                    }

                    this.index++;
                }

                e.target.value = '';
            },

            removeImage: function(id, index){
                if (id != null) {
                    axios.delete(route('gallery.destroy', id))
                    .then(response => {
                        Vue.delete(this.images, index);
                    })
                    .catch(error => console.error(error));
                } else {
                    Vue.delete(this.images, index);
                }
            }
        }
    }
</script>

<style lang="scss">
    .upload-image{
        padding: 5px;
        cursor: pointer;
        min-height: 200px;
        border-radius: 5px;
        background-color: rgba(255, 153, 0, 0.6);
    }
    .upload-image.dragover{
        filter: brightness(30);
    }

    .upload-image > div.form {
        min-height: 200px;
        position: relative;
    }

    .upload-image > div.form > input {
        display: none;
    }

    .upload-image > div.form > div.text-center {
        text-align: center;
        position: absolute;
        bottom: 0;
        width: 100%;
    }

    .upload-image-thumbnails {
        margin-bottom: 1em;
    }
    .upload-image-thumbnail {
        border-radius: 2.5px;
        position:relative;
        width:20%;
        padding:14.7%;
        overflow: hidden;
        margin:10px;
        display:inline-block;
        background-color: whitesmoke;

    }

    .upload-image-thumbnail div.btn-group {
        top: 40%;
        text-align: center;
        left: 0;
        right: 0;
        position: absolute;
        display: inline-block;
        z-index: 999;
        opacity: 0;
    }

    .upload-image-thumbnail img{
        position: absolute;
        top: 50%;
        left: 50%;
        max-width: 100%;
        max-height: 100%;
        opacity: 0;
        transform: translateX(-50%) translateY(-50%);
        transition: 1s opacity;
        z-index: 99;
    }
    .upload-image-thumbnail:hover {
        img {
            filter: blur(2px);
        }

        div.btn-group {
            opacity: 1;
        }
    }

    .upload-image-thumbnail img.show{
        opacity: 1;
    }
    .upload-image-thumbnail.bad-size img{
        filter: grayscale(100%);
    }

    .upload-image-thumbnail span{
        position: absolute;
        top: -5px;
        left: 0px;
        z-index: 100;
        padding: 0px 1px;
        border-radius: 2px;
        background-color: grey;
    }
</style>
