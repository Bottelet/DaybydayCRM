import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue2'
import laravel from 'laravel-vite-plugin'
import { copyFileSync, mkdirSync } from 'fs'
import { resolve } from 'path'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/js/app.js',
                'resources/assets/js/jquery-init.js',
                'resources/assets/sass/app.scss',
                'resources/assets/sass/vendor.scss',
            ],
            refresh: true,
        }),
        vue(),
        {
            name: 'copy-bootstrap-fonts',
            apply: 'build',
            writeBundle() {
                const srcDir = resolve('node_modules/bootstrap-sass/assets/fonts/bootstrap')
                const destDir = resolve('public/fonts/bootstrap')
                try {
                    mkdirSync(destDir, { recursive: true })
                    const fs = require('fs')
                    const files = fs.readdirSync(srcDir)
                    files.forEach(file => {
                        copyFileSync(
                            resolve(srcDir, file),
                            resolve(destDir, file)
                        )
                    })
                } catch (err) {
                    console.warn('Failed to copy bootstrap fonts:', err.message)
                }
            }
        }
    ],
    resolve: {
        alias: {
            vue$: 'vue/dist/vue.esm.js',
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                loadPaths: [resolve('node_modules')],
            }
        }
    },
    server: {
        middlewareMode: true,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
    },
})

