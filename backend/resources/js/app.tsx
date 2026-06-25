import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import AuthLayout from './layouts/AuthLayout'
import AppLayout from './layouts/AppLayout'

createInertiaApp({
  resolve: (name) => {
    const page = resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx'))
    return page.then((m: any) => {
      if (name === 'Login' || name === 'PublicForm') {
        // no layout wrapper - these pages handle their own full-page layout
      } else {
        m.default.layout = (page: React.ReactNode) => <AppLayout>{page}</AppLayout>
      }
      return m
    })
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />)
  },
  progress: { color: '#C8A45C' },
})
