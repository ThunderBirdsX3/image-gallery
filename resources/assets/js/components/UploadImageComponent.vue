<template>
    <div>
        <div class="upload-image" v-bind:class="{ 'dragover': onDragover }">
            <div class="form" id="upload_image_form">
                <i class="material-icons">cloud_upload</i>
                <h4>Drop files here or click to choose files.</h4>
                <input type="file" id="upload_image_form_input" multiple />
            </div>
        </div>

        <div class="upload-image-thumbnails row">
            <div v-for="(image, index) in images" class="col-md-4">

                <div class="upload-image-thumbnail">
                    {{ index }}
                    <div class="btn-group" v-if="image.src != null">
                        <a v-bind:href="url + '/' + image.src" v-bind:data-lightbox="image.src" class="btn btn-info btn-sm">
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

                    <div class="bad-file" v-if="image.bad_size">
                        <i class="material-icons">warning</i>
                        <p>File size exceeded - {{ image.name }}</p>
                    </div>
                    <div class="bad-file" v-else-if="image.bad_type">
                        <i class="material-icons">warning</i>
                        <p>File type not supported - {{ image.name }}</p>
                    </div>
                    <div v-else-if="image.src != null">
                        <img v-bind:src="url + '/' + image.src" class="show">
                    </div>
                    <div v-else class="progress">
                        <progress max="100" :value.prop="image.percent"></progress>
                    </div>

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

            ['drop']
            .forEach(event => this.form.addEventListener(event, this.fileDrop));

            ['change']
            .forEach(event => this.input.addEventListener(event, this.fileDrop));

            this.form.addEventListener('click', (e) => {
                this.input.click();
            });
        },
        methods: {
            uploadFile: function(file, index){

                let image = new FormData();
                image.append('image', file);

                axios.post(route('gallery.store'), image, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: function( progressEvent ) {
                        this.images[index]['percent'] = parseInt( Math.round( ( progressEvent.loaded * 100 ) / progressEvent.total ) );
                    }.bind(this)
                })
                .then((response) => {

                    this.images[index]['id'] = response.data.id;
                    this.images[index]['src'] = response.data.src;
                    this.images[index]['uploaded'] = true;

                })
                .catch((error) => {
                    console.error(error);
                });
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
                        'name': files[i].name,
                        'percent': 0,
                    })

                    if((files[i].size * 0.001) > this.max_filesize) {
                        this.images[this.index]['bad_size'] = true;
                        console.log('bad_size');
                    } else if (! /\.(jpe?g|png)$/i.test( files[i].name ) ) {
                        this.images[this.index]['bad_type'] = true;
                        console.log('bad_type');
                    } else {
                        this.uploadFile(files[i], this.index);
                    }

                    this.index++;
                }

                e.target.value = '';
            },

            removeImage: function(id, index){

                console.log('Remove', id, index)

                if (id != null) {
                    axios.delete(route('gallery.destroy', id))
                    .catch(error => console.error(error));
                }

                Vue.delete(this.images, index);
                this.index--;
            }
        }
    }
</script>

<style lang="scss">
    .upload-image{
        padding: 5px;
        cursor: pointer;
        min-height: 200px;
        border-radius: 10px;
        border: 5px dashed gray;
        text-align: center;
        color: gray;

        i {
            font-size: 100pt;
        }
    }
    .upload-image:hover {
        border-color: black;
        color: black;
    }

    .upload-image > div.form {
        min-height: 200px;
        position: relative;
    }

    .upload-image > div.form > input {
        display: none;
    }

    .col-md-4 {
        padding: 0;
    }

    .upload-image-thumbnails {
        margin: 20px 0;
    }

    .upload-image-thumbnail {
        border-radius: 5px;
        overflow: hidden;
        margin: 5px;
        height: 200px;
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
        max-width: 95%;
        max-height: 95%;
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

    .bad-file {
        padding-top: 15%;
        color: red;
        text-align: center;
        vertical-align: middle;

        i {
            font-size: 100px
        }
    }

    progress {
        text-align: center;
        width: 100%;
    }
</style>
