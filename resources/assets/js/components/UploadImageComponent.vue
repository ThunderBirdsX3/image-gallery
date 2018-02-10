<template>
    <div>
        <div class="upload-image" v-bind:class="{ 'dragover': onDragover }">
            <!-- <form v-bind:id="'upload_image_form-' + name" enctype="multipart/form-data"> -->
            <div class="form" id="upload_image_form">
                <input type="file" id="upload_image_form_input" multiple accept="image/*" />
                <!-- <div class="text-center">
                    <button type="submit"
                        v-bind:class="button_class"
                        v-on:click="submit"
                        v-bind:disabled="onUploading"
                        v-html="button_html"></button>
                </div> -->
            </div>
            <!-- </form> -->
        </div>

        <div class="upload-image-thumbnails">
            <div v-for="(value, key) in files" class="upload-image-thumbnail" v-on:click="fileView($event, key)"
                    v-bind:class="{ 'uploaded': value.uploaded, 'bad-size': value.bad_size }" >
                <span v-on:click="fileDelete($event, key)">
                &#x2716;
                </span>
                <img v-bind:src="image[key]" v-bind:class="{ 'show': image[key]}">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'upload-image',
        props: {
            max_filesize: {
                type: Number,
                required: false,
                default: 1000
            },
            button_html: {
                type: String,
                required: false,
                default: 'Upload Images'
            },
            button_class: {
                type: String,
                required: false,
                default: 'btn btn-primary btn-sm'
            },
        },
        data: function(){
            return {
                form: null,
                input: null,
                index: 0,
                total: 0,
                files: [],
                image: [],
                batch: [],
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
                    // keys.forEach((key) => {
                    //     Vue.set(this.files[key], 'uploaded', true);
                    //
                    //     this.total++;
                    // });
                    // delete this.batch;

                    // this.$emit('upload-image-success', [formData, response]);
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

                for(let i = 0; i < files.length; i++){

                    this.image[this.index] = {
                        'bad_size': false,
                        'bad_type': false,
                        'uploaded': false,
                        'src': null,
                        'percent': false,
                    }

                    if((files[i].size * 0.001) > this.max_filesize) {
                        this.image[this.index]['bad_size'] = true;
                        console.log('bad_size');
                    } else if (! /\.(jpe?g|png|gif)$/i.test( files[i].name ) ) {
                        this.image[this.index]['bad_type'] = true;
                        console.log('bad_type');
                    } else {
                        this._xhr(files[i], this.index);
                    }

                    this.index++;
                }

                e.target.value = '';
            },

            fileDelete: function(e, key){
                Vue.delete(this.files, key);
                Vue.delete(this.image, key);
            },
            fileView: function(e, key){
                e.preventDefault(); e.stopPropagation();
            }
        }
    }
</script>

<style lang="css">
    .upload-image{
        padding: 5px;
        cursor: pointer;
        min-height: 200px;
        border-radius: 5px;
        background-color: rgba(255, 153, 0, 0.6);
    }
    .upload-image.dragover{}

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

    .upload-image-thumbnails{
        margin-bottom: 1em;
    }
    .upload-image-thumbnail{
        border-radius: 2.5px;
        position:relative;
        width:20%;
        padding:14.7%;
        overflow: hidden;
        margin:10px;
        display:inline-block;
        background-color: rgba(255, 255, 255, 0.2);
    }

    .upload-image-thumbnail img{
        position: absolute;
        top:50%;
        left: 50%;
        /*min-width: 100%;*/
        max-width: 100%;
        /*min-height: 100%;*/
        max-height: 100%;
        /*max-height: 150%;*/
        opacity: 0;
        transform: translateX(-50%) translateY(-50%);
        transition: 1s opacity;
    }
    .upload-image-thumbnail img.show{
        opacity: 1;
    }
    .upload-image-thumbnail img:hover{
        filter: blur(2px);
    }
    .upload-image-thumbnail.bad-size img{
        filter: grayscale(100%);
    }
    .upload-image-thumbnail.uploaded img{
        opacity: 0.1;
        filter: green;
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
