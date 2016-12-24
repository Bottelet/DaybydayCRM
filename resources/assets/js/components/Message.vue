<template>
</template>

<script>
  export default {
    props:['message', 'type'],
    methods: {
      openNotification() {
        this.$message({
          message: this.message,
          type: this.type,
          duration: 5000,
          showClose:true
        });
      }
    },
    mounted() {
      this.openNotification();
    }
  }
</script>