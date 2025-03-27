
const Captcha = {
    template: `
    <div :id="uuid">
        <div class="ba-click-captcha" :style="{'top':captchaBoxTop,'left':captchaBoxLeft}">
            <div v-if="state.loading" class="loading">加载中</div>
            <div v-else class="captcha-img-box">
                <img class="captcha-img" :style="{width:state.captcha.width+'px',height:state.captcha.height+'px'}" @click.prevent="onRecord($event)" :src="state.captcha.base64" />
                <span v-for="(item, index) in state.xy" :key="index" class="step" @click="onCancelRecord(index)" :style="{'left':parseFloat(item.split(',')[0]) - 13+'px','top':parseFloat(item.split(',')[1]) - 13+'px'}"
                > {{ index + 1 }} </span>
            </div>
            <div class="captcha-prompt" v-if="state.tip">{{ state.tip }}</div>
            <div v-else class="captcha-prompt">
                请点击
                <span v-for="(text, index) in state.captcha.text" :key="index" :class="state.xy.length > index ? 'clicaptcha-clicked' : ''"> {{ text }} </span>
            </div>
            <div class="captcha-refresh-box">
                <div class="captcha-refresh-line captcha-refresh-line-l"></div>
                <i class="fa fa-refresh captcha-refresh-btn" title="刷新" @click="load"></i>
                <div class="captcha-refresh-line captcha-refresh-line-r"></div>
            </div>
        </div>
        <div class="ba-layout-shade" @click="onClose"></div>
    </div>`,
    props: {
        uuid: {
            type: String,
            default: '',
        },
        callback: {
            type: Function,
            default: null,
        },
        unset: {
            type: Boolean,
            default: false,
        },
        success: {
            type: String,
            default: '验证通过',
        },
        error: {
            type: String,
            default: '验证失败',
        },
    },
    setup(props) {
        const { reactive, computed } = Vue

        const state = reactive({
            loading: true,
            xy: [],
            tip: '',
            captcha: {
                id: '',
                text: '',
                base64: '',
                width: 350,
                height: 200,
            },
        })

        function getCaptchaData(id) {
            return createAxios({
                url: '/api/common/clickCaptcha',
                method: 'get',
                params: {
                    id,
                },
            })
        }

        function checkClickCaptcha(id, info, unset) {
            return createAxios(
                {
                    url: '/api/common/checkClickCaptcha',
                    method: 'post',
                    data: {
                        id,
                        info,
                        unset,
                    },
                },
                {
                    showCodeMessage: false,
                }
            )
        }

        const load = () => {
            state.loading = true
            getCaptchaData(props.uuid).then((res) => {
                state.xy = []
                state.tip = ''
                state.loading = false
                state.captcha = res.data
            })
        }

        const onRecord = (event) => {
            if (state.xy.length < state.captcha.text.length) {
                state.xy.push(event.offsetX + ',' + event.offsetY)
                if (state.xy.length == state.captcha.text.length) {
                    const captchaInfo = [state.xy.join('-'), (event.target).width, (event.target).height].join(';')
                    checkClickCaptcha(props.uuid, captchaInfo, props.unset)
                        .then(() => {
                            state.tip = props.success
                            setTimeout(() => {
                                props.callback?.(captchaInfo)
                                onClose()
                            }, 1500)
                        })
                        .catch(() => {
                            state.tip = props.error
                            setTimeout(() => {
                                load()
                            }, 1500)
                        })
                }
            }
        }

        const onCancelRecord = (index) => {
            state.xy.splice(index, 1)
        }

        const onClose = () => {
            document.getElementById(props.uuid)?.remove()
        }

        const captchaBoxTop = computed(() => 'calc(50% - ' + (state.captcha.height + 200) / 2 + 'px)')
        const captchaBoxLeft = computed(() => 'calc(50% - ' + (state.captcha.width + 24) / 2 + 'px)')

        load()

        return {
            state,
            captchaBoxTop,
            captchaBoxLeft,
            load,
            onRecord,
            onCancelRecord,
            onClose,
        }
    }
}