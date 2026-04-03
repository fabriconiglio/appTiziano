'use client'

import { useState } from 'react'
import Link from 'next/link'
import { CheckCircle, Loader2, AlertTriangle } from 'lucide-react'
import { submitArrepentimiento } from '@/lib/api'

export default function ArrepentimientoPage() {
  const [form, setForm] = useState({ name: '', email: '', order_number: '', reason: '' })
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [result, setResult] = useState<{ message: string; code: string } | null>(null)

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setLoading(true)
    setError(null)
    try {
      const res = await submitArrepentimiento({
        name: form.name,
        email: form.email,
        order_number: form.order_number,
        reason: form.reason || undefined,
      })
      setResult(res)
    } catch (err: unknown) {
      const apiErr = err as { message?: string }
      setError(apiErr?.message || 'Ocurrió un error al procesar tu solicitud. Intentá de nuevo.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <div
        style={{
          background: 'var(--color-dark)',
          padding: '48px 0 40px',
          borderBottom: '3px solid var(--color-primary)',
        }}
      >
        <div className="max-w-7xl mx-auto px-6">
          <nav className="mb-4 flex items-center gap-2 text-xs" style={{ color: '#888' }}>
            <Link href="/" style={{ color: '#888' }}>Inicio</Link>
            <span>/</span>
            <span style={{ color: 'var(--color-primary)' }}>Botón de Arrepentimiento</span>
          </nav>
          <h1
            style={{
              fontFamily: 'var(--font-display)',
              fontSize: 'clamp(2rem, 5vw, 3rem)',
              color: 'var(--color-white)',
              fontStyle: 'italic',
            }}
          >
            Botón de Arrepentimiento
          </h1>
        </div>
      </div>

      <div style={{ background: 'var(--color-bg)' }} className="py-12">
        <div className="max-w-2xl mx-auto px-6">
          {result ? (
            <div className="text-center py-8">
              <CheckCircle size={56} style={{ color: '#2E7D52' }} className="mx-auto mb-4" />
              <h2
                className="text-2xl font-semibold mb-3"
                style={{ color: 'var(--color-dark)', fontFamily: 'var(--font-display)', fontStyle: 'italic' }}
              >
                Solicitud registrada
              </h2>
              <p className="text-sm mb-4" style={{ color: 'var(--color-dark-soft)' }}>
                {result.message}
              </p>
              <div
                className="inline-block px-6 py-3 mb-6 text-sm font-bold"
                style={{
                  background: '#F0FDF4',
                  border: '1px solid #BBF7D0',
                  color: '#166534',
                }}
              >
                Código de gestión: <strong>{result.code}</strong>
              </div>
              <p className="text-sm mb-8" style={{ color: 'var(--color-dark-soft)' }}>
                Te enviamos un email con la confirmación y el código de gestión.
                Guardalo como comprobante.
              </p>
              <Link
                href="/"
                className="inline-block py-3 px-8 text-sm font-bold uppercase tracking-widest"
                style={{ background: 'var(--color-dark)', color: 'var(--color-white)' }}
              >
                Volver al inicio
              </Link>
            </div>
          ) : (
            <>
              <div
                className="p-5 mb-8 text-sm leading-relaxed"
                style={{
                  background: 'var(--color-white)',
                  border: '1px solid var(--color-border)',
                  color: 'var(--color-dark-soft)',
                }}
              >
                <h2
                  className="text-base font-semibold mb-3"
                  style={{ color: 'var(--color-dark)' }}
                >
                  Derecho de revocación (Art. 34, Ley 24.240)
                </h2>
                <p className="mb-2">
                  El consumidor tiene derecho a revocar la aceptación de una compra realizada
                  a distancia dentro de los <strong>10 (diez) días corridos</strong> contados a
                  partir de la fecha de entrega del bien o de la celebración del contrato, lo último
                  que ocurra, sin responsabilidad alguna y sin necesidad de indicar motivo.
                </p>
                <p>
                  Para ejercer este derecho, completá el siguiente formulario. No es necesario
                  estar registrado. Recibirás un código de gestión de tu solicitud por email
                  dentro de las 24 horas.
                </p>
              </div>

              {error && (
                <div
                  className="flex items-center gap-3 p-4 mb-6 text-sm"
                  style={{
                    background: '#FEF2F2',
                    border: '1px solid #FECACA',
                    color: '#991B1B',
                  }}
                >
                  <AlertTriangle size={18} className="shrink-0" />
                  <span>{error}</span>
                </div>
              )}

              <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                <div>
                  <label
                    htmlFor="name"
                    className="block text-xs uppercase tracking-wider font-semibold mb-2"
                    style={{ color: 'var(--color-dark)' }}
                  >
                    Nombre completo *
                  </label>
                  <input
                    id="name"
                    type="text"
                    required
                    value={form.name}
                    onChange={(e) => setForm({ ...form, name: e.target.value })}
                    className="w-full px-4 py-3 text-sm outline-none"
                    style={{
                      border: '1px solid var(--color-border)',
                      background: 'var(--color-white)',
                      color: 'var(--color-dark)',
                    }}
                    placeholder="Tu nombre y apellido"
                  />
                </div>

                <div>
                  <label
                    htmlFor="email"
                    className="block text-xs uppercase tracking-wider font-semibold mb-2"
                    style={{ color: 'var(--color-dark)' }}
                  >
                    Email *
                  </label>
                  <input
                    id="email"
                    type="email"
                    required
                    value={form.email}
                    onChange={(e) => setForm({ ...form, email: e.target.value })}
                    className="w-full px-4 py-3 text-sm outline-none"
                    style={{
                      border: '1px solid var(--color-border)',
                      background: 'var(--color-white)',
                      color: 'var(--color-dark)',
                    }}
                    placeholder="tu@email.com"
                  />
                </div>

                <div>
                  <label
                    htmlFor="order_number"
                    className="block text-xs uppercase tracking-wider font-semibold mb-2"
                    style={{ color: 'var(--color-dark)' }}
                  >
                    Número de pedido *
                  </label>
                  <input
                    id="order_number"
                    type="text"
                    required
                    value={form.order_number}
                    onChange={(e) => setForm({ ...form, order_number: e.target.value })}
                    className="w-full px-4 py-3 text-sm outline-none"
                    style={{
                      border: '1px solid var(--color-border)',
                      background: 'var(--color-white)',
                      color: 'var(--color-dark)',
                    }}
                    placeholder="Ej: TIZ-250403-A1B2"
                  />
                </div>

                <div>
                  <label
                    htmlFor="reason"
                    className="block text-xs uppercase tracking-wider font-semibold mb-2"
                    style={{ color: 'var(--color-dark)' }}
                  >
                    Motivo (opcional)
                  </label>
                  <textarea
                    id="reason"
                    rows={3}
                    value={form.reason}
                    onChange={(e) => setForm({ ...form, reason: e.target.value })}
                    className="w-full px-4 py-3 text-sm outline-none resize-none"
                    style={{
                      border: '1px solid var(--color-border)',
                      background: 'var(--color-white)',
                      color: 'var(--color-dark)',
                    }}
                    placeholder="Contanos el motivo si lo deseás"
                  />
                </div>

                <button
                  type="submit"
                  disabled={loading}
                  className="w-full py-4 text-sm font-bold uppercase tracking-widest flex items-center justify-center gap-2 disabled:opacity-50"
                  style={{
                    background: 'var(--color-dark)',
                    color: 'var(--color-white)',
                  }}
                >
                  {loading ? (
                    <>
                      <Loader2 size={16} className="animate-spin" />
                      Procesando...
                    </>
                  ) : (
                    'Solicitar arrepentimiento'
                  )}
                </button>
              </form>
            </>
          )}
        </div>
      </div>
    </>
  )
}
