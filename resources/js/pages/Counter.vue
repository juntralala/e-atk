<script setup>
import { computed, createVNode, h, ref } from 'vue';
// cuman buat ngetes cara kerja rerender di vue
const count = ref(0);
const count2 = 0;

const countValue = computed(() => count.value);
const count2Value = computed(() => count2);

// const myNode = h('div', {style: "background-color: red; width: 20; height:20", class: "w-20 h-20 bg-blue"}, "ini konten");
const myNode = createVNode('div', {style: "background-color: red; width: 20; height:20", class: "w-20 h-20 bg-blue"}, "ini konten");
const MyComponent = () => h('div', {style: "background-color: red; width: 20; height:20", class: "w-20 h-20 bg-blue"}, `${count.value}`);
function Hello(a) {
  return 'Hello ' + JSON.stringify(a);
}
</script>
<template>
  <!-- {{ myNode }} -->
  <!-- <component :is="myNode"/> <!-- <- reference ke object yang sama jadi nggak pernah di update, compare vnode pun nggak pernah -->
  <!-- <MyComponent/> -->
  <h1 :value="count">{{ countValue }} - <span>{{ count2 }}</span></h1>
  <h1>{{ count2Value }}</h1>
  <v-btn @click="count++">{{ count }} - {{ count2 }}</v-btn>
  <v-btn @click="count2++">{{ count2 }}</v-btn>
  <Hello :p="2"/>
</template>