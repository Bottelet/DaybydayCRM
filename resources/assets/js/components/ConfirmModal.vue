<template>
    <transition name="appear">
        <div class="modal-confirmation">
            <div class="modal-confirmation-window">
                    <div class="modal-header">
                        <h4 id="myModalLabel" class="modal-title">{{trans(title)}}</h4></div>
                    <div class="modal-footer">
                        <button type="button" data-dismiss="modal" class="btn btn-default col-lg-6" @click="onCancel">{{trans("Cancel")}}</button>
                        <div class="col-lg-6">
                            <button type="button" data-dismiss="modal" class="btn btn-brand form-control closebtn" @click="onConfirm">{{trans("Confirm")}}</button>
                        </div>
                    </div>
                </div>
        </div>
    </transition>
</template>
<script>
    export default {
        props: ["title"],
        methods: {
            onConfirm() {
                this.$emit("confirm");
            },
            onCancel() {
                this.$emit("cancel");
            }
        }
    }
</script>
<style>
    .modal-confirmation {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background: rgba(0, 0, 0, .5);
        z-index: 1050;
    }

    .modal-confirmation-window {
        position: absolute;
        top: 50%;
        left: 50%;
        transition: .5s;
        width: 100%;
        min-width: 400px;
        max-width: 600px;
        background: white;
        box-shadow: 0 0 10px rgba(0, 0, 0, .5);
        transform: translate(-50%, -50%);
        padding: 1em;
        color: black;
        text-align: center;
    }

    .modal-confirmation-window .actions {
        display: flex;
        justify-content: flex-end;
    }

    .actions button {
        font-size: inherit;
        margin: 4px;
        border: none;
        padding: 6px 8px;
        cursor: pointer;
    }

    .actions .cancel {
        background: darkred;
        color: white;
    }

    .actions .confirm {
        background: darkcyan;
        color: white;
    }

    .appear-enter {
        opacity: 0 !important;
    }

    .appear-enter .modal-confirmation-window {
        transform: translate(-75%, -50%);
    }

    .appear-enter-active {
        transition: .5s;
    }

    .appear-leave-active .modal-confirmation-window {
        transform: translate(0, -50%);
    }

    .appear-leave-active {
        opacity: 0;
        transition: .5s;
    }
</style>
