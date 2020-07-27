<template>
    <div class="wrap google-api">
        <h2>{{ pageTitle }}</h2>

        <div class="page_body">
            <div class="page-inner">

                <template v-if="token">
                    <h4>{{ 'Токен существует' }}</h4>
                </template>

                <div v-else>
                    <a :href="authUrl" target="_blank" class="auth-link">{{ 'Авторизоваться' }}</a>
                    <div>
                        <input class="mb10" type="text" size="60" name="google-api" v-model="models.token"
                               placeholder="Token" />
                    </div>
                </div>

                <div>
                    <input class="mb10" type="text" size="60" name="google-api" v-model="models.sheetId"
                           placeholder="Sheet Id" />
                </div>

                <div>
                    <input class="mb10" type="text" size="60" name="google-api" v-model="models.tabName"
                           placeholder="Tab Name" />
                </div>

                <div class="columns">
                    <div class="title">Google doc columns</div>
                    <div class="button button-primary add" @click="add">add</div>
                    <table>
                        <thead>
                        <tr>
                            <th>key</th>
                            <th>value</th>
                            <th>remove</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="models.columns" v-for="(item, index) in models.columns">
                            <td><input type="text" v-model="item.key"></td>
                            <td><input type="text" v-model="item.value"></td>
                            <td>
                                <div class="remove" @click="remove(index)">x</div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="options">
                <button type="button" name="submit"
                        @click="saveData"
                        class="button button-primary api-submit">{{ 'Сохранить' }}
                </button>
                <div class="updated-notice" v-if="saved"><p>{{ 'Data saved!' }}</p></div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import * as qs from 'qs';

    axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';

    /* global connector */
    export default {
        name: "googleApi",
        data() {
            return {
                pageTitle: connector.page_title,
                authUrl: connector.authUrl,
                token: connector.token,
                saved: false,
                models: {
                    token: '',
                    sheetId: connector.sheetId,
                    tabName: connector.tabName,
                    columns: connector.columns || [],
                }
            }
        },
        mounted() {
            // console.log(window);
        },
        methods: {
            saveData() {
                let data = {
                    action: 'saveGoogleConnectorData',
                    fields: this.models,
                };

                this.sendRequest(data).then(res => {
                    if (res.data.success) {
                        this.saved = true;
                    }
                });
            },
            sendRequest(requestBody) {
                console.log(window.ajaxurl);
                return axios.post(window.ajaxurl, qs.stringify(requestBody));
            },
            remove(index) {
                this.models.columns.splice(index, 1)
            },
            add() {
                this.models.columns.push({
                    key: '',
                    value: '',
                });
            }
        },
    }
</script>

<style lang="scss" scoped>
    .google-api {
        .page-inner {
            .auth-link {
                display: inline-block;
                margin-bottom: 16px;
            }

            .api-submit {
                margin-top: 16px;
            }

            .updated-notice {
                margin-top: 10px;
            }
        }
        .button.add {
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .columns {
            .title {
                font-size: 16px;
                margin-top: 20px;
            }
        }
        .api-submit {
            margin-top: 20px;
        }
        .mb10 {
            margin-bottom: 10px;
        }

        .remove {
            padding: 8px 0;
            background: red;
            cursor: pointer;
            color: #fff;
            text-align: center;
        }
    }
</style>