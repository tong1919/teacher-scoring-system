import {
    Confirm
} from 'element-ui'

/**
 * 
 * @param {*} param0 
 */
export default function ({
    title = "提示",
    content = "确定吗？",
    type = "warning",
}) {
    return new Promise((resolve, reject) => {
        Confirm(content, title, {
                confirmButtonText: "确定",
                cancelButtonText: "取消",
                type,
            })
            .then(() => {
                resolve()
            })
            .catch(() => {
                reject()
            })
    })
}