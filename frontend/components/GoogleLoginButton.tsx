'use client'

import { useEffect, useRef, useCallback } from 'react'
import { useRouter } from 'next/navigation'
import { useAuth } from '@/lib/AuthContext'

export default function GoogleLoginButton() {
  const { loginWithGoogle } = useAuth()
  const router = useRouter()
  const buttonRef = useRef<HTMLDivElement>(null)
  const initializedRef = useRef(false)

  const handleCredentialResponse = useCallback(
    async (response: { credential: string }) => {
      try {
        await loginWithGoogle(response.credential)
        router.push('/mi-cuenta')
      } catch {
        alert('Error al iniciar sesión con Google. Intentá de nuevo.')
      }
    },
    [loginWithGoogle, router],
  )

  useEffect(() => {
    const clientId = process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID
    if (!clientId || initializedRef.current) return

    const script = document.createElement('script')
    script.src = 'https://accounts.google.com/gsi/client'
    script.async = true
    script.defer = true
    script.onload = () => {
      const g = (globalThis as any).google
      if (!g || !buttonRef.current) return
      initializedRef.current = true

      g.accounts.id.initialize({
        client_id: clientId,
        callback: handleCredentialResponse,
      })

      g.accounts.id.renderButton(buttonRef.current, {
        type: 'standard',
        theme: 'outline',
        size: 'large',
        text: 'continue_with',
        shape: 'rectangular',
        width: buttonRef.current.offsetWidth,
        locale: 'es',
      })
    }
    document.head.appendChild(script)

    return () => {
      script.remove()
    }
  }, [handleCredentialResponse])

  if (!process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID) return null

  return <div ref={buttonRef} className="w-full flex justify-center" />
}
