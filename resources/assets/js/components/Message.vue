<template>
</template>

<script>
  export default {
    props:['message', 'type'],
    methods: {
      openNotification() {
        this.$message({
          message: this.message,
          type: this.type
        });
      }
    },
    mounted() {
      this.openNotification();
    }
  }
</script>