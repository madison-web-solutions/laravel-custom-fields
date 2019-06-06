<template>
    <div class="lcf-input lcf-input-lat-lng">
        <input ref="lat" type="text" :name="name + '[lat]'" :value="lat" @change="change" />
        <input ref="lng" type="text" :name="name + '[lng]'" :value="lng" @change="change" />
    </div>
</template>

<script>
import inputMixin from '../../input-mixin.js';
import { get } from 'lodash-es';
export default {
    mixins: [inputMixin],
    computed: {
        lat: function() {
            return get(this.value, 'lat');
        },
        lng: function() {
            return get(this.value, 'lng');
        },
    },
    methods: {
        change: function(e) {
            this.$emit('change', {key: this._key, value: {lat: this.$refs.lat.value, lng: this.$refs.lng.value}});
        }
    }
};
</script>
