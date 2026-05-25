import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue2'
import laravel from 'laravel-vite-plugin'
import { copyFileSync, mkdirSync, existsSync } from 'fs'
import { resolve } from 'path'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/assets/js/app.js',
                'resources/assets/sass/app.scss',
                'resources/assets/sass/vendor.scss',
            ],
            refresh: true,
        }),
        vue(),
        {
            // Copy jQuery to public/js so it can be loaded as a classic (non-module)
            // script before @vite. This ensures window.jQuery is available for legacy
            // plugins (dataTables, caret, etc.) which run before ES module scripts.
            name: 'copy-jquery',
            buildStart() {
                try {
                    copyFileSync(
                        resolve('node_modules/jquery/dist/jquery.min.js'),
                        resolve('public/js/jquery.min.js')
                    )
                } catch (err) {
                    console.warn('Failed to copy jquery.min.js:', err.message)
                }
            },
            writeBundle() {
                try {
                    copyFileSync(
                        resolve('node_modules/jquery/dist/jquery.min.js'),
                        resolve('public/js/jquery.min.js')
                    )
                } catch (err) {
                    console.warn('Failed to copy jquery.min.js on writeBundle:', err.message)
                }
            }
        },
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
        },
        {
            name: 'move-manifest',
            apply: 'build',
            writeBundle() {
                // Move manifest from .vite/ to root build directory
                const srcManifest = resolve('public/build/.vite/manifest.json')
                const destManifest = resolve('public/build/manifest.json')
                try {
                    if (existsSync(srcManifest)) {
                        copyFileSync(srcManifest, destManifest)
                        console.log('✓ Manifest moved to public/build/manifest.json')
                    }
                } catch (err) {
                    console.warn('Failed to move manifest:', err.message)
                }
            }
        }
    ],
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            }
        }
    },
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

