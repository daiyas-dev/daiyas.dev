<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">サンプルVueコンポーネント</div>

                    <div class="card-body">
                        コンポーネントの内部だよ
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ExampleComponent",
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
