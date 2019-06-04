<template>
    <div class="lcf-input lcf-input-lat-lng">
        <input ref="lat" type="text" :name="this.name + '[lat]'" :value="lat" @change="change" />
        <input ref="lng" type="text" :name="this.name + '[lng]'" :value="lng" @change="change" />
    </div>
</template>

<script>
import { get } from 'lodash-es';
export default {
    props: ['settings', 'value', 'hasError'],
    computed: {
        name: function() {
            return get(this.settings, 'name');
        },
        lat: function() {
            return get(this.value, 'lat');
        },
        lng: function() {
            return get(this.value, 'lng');
        },
        _key: function() {
            return get(this.settings, 'key', this.$vnode.key);
        }
    },
    methods: {
        change: function(e) {
            this.$emit('change', {key: this._key, value: {lat: this.$refs.lat.value, lng: this.$refs.lng.value}});
        }
    }
};
</script>
