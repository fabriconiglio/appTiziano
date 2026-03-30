import { getProduct, getProducts, formatPrice } from '@/lib/api'
import { notFound } from 'next/navigation'
import Link from 'next/link'
import { ShoppingBag, ChevronRight, Package, Tag, Star, ArrowLeft } from 'lucide-react'
import ProductCard from '@/components/products/ProductCard'

interface PageProps {
  params: Promise<{ id: string }>
}

export default async function ProductDetailPage({ params }: PageProps) {
  const { id } = await params
  const productId = parseInt(id)

  if (isNaN(productId)) notFound()

  let product
  try {
    product = await getProduct(productId)
  } catch {
    notFound()
  }

  const relatedData = await getProducts({
    category_id: product.category_id ?? undefined,
  }).catch(() => null)

  const related = relatedData?.data.filter((p) => p.id !== product.id).slice(0, 4) ?? []
  const price = formatPrice(product.price)
  const inStock = product.current_stock > 0

  return (
    <>
      {/* Breadcrumb bar */}
      <div
        style={{
          background: 'var(--color-dark)',
          padding: '20px 0',
          borderBottom: '2px solid var(--color-primary)',
        }}
      >
        <div className="max-w-7xl mx-auto px-6">
          <nav className="flex items-center gap-2 text-xs" style={{ color: '#888' }}>
            <Link href="/" style={{ color: '#888' }}>Inicio</Link>
            <ChevronRight size={12} />
            <Link href="/productos" style={{ color: '#888' }}>Productos</Link>
            <ChevronRight size={12} />
            <span style={{ color: 'var(--color-primary)' }}>{product.name}</span>
          </nav>
        </div>
      </div>

      {/* Product detail */}
      <section style={{ background: 'var(--color-bg)', padding: '60px 0' }}>
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid lg:grid-cols-2 gap-12 items-start">
            {/* Image */}
            <div
              className="flex items-center justify-center overflow-hidden"
              style={{
                aspectRatio: '1/1',
                background: 'linear-gradient(135deg, var(--color-cream) 0%, var(--color-primary-light) 100%)',
                border: '1px solid var(--color-border)',
              }}
            >
              {product.image_url ? (
                <img
                  src={product.image_url}
                  alt={product.name}
                  className="w-full h-full object-cover"
                  loading="eager"
                />
              ) : (
                <div className="text-center p-8">
                  <div
                    className="w-32 h-32 rounded-full mx-auto mb-4"
                    style={{ background: 'var(--color-primary)', opacity: 0.3 }}
                  />
                  <p
                    className="text-sm uppercase tracking-widest font-semibold"
                    style={{ color: 'var(--color-primary-dark)' }}
                  >
                    {product.brand?.name ?? 'Tiziano'}
                  </p>
                </div>
              )}
            </div>

            {/* Info */}
            <div>
              {/* Category */}
              {product.category && (
                <Link
                  href={`/categorias/${product.category.slug || product.category.id}`}
                  className="inline-block text-xs font-semibold uppercase tracking-widest mb-4 px-3 py-1.5"
                  style={{ background: 'var(--color-cream)', color: 'var(--color-dark-soft)', border: '1px solid var(--color-border)' }}
                >
                  {product.category.name}
                </Link>
              )}

              <h1
                className="mb-3"
                style={{
                  fontFamily: 'var(--font-display)',
                  fontSize: 'clamp(1.75rem, 4vw, 2.5rem)',
                  color: 'var(--color-dark)',
                  lineHeight: 1.1,
                }}
              >
                {product.name}
              </h1>

              {/* Brand */}
              {product.brand && (
                <p
                  className="text-sm italic mb-4"
                  style={{ color: 'var(--color-primary-dark)' }}
                >
                  por {product.brand.name}
                </p>
              )}

              {/* Stars */}
              <div className="flex gap-1 mb-5">
                {Array.from({ length: 5 }).map((_, i) => (
                  <Star key={i} size={14} fill="var(--color-primary)" style={{ color: 'var(--color-primary)' }} />
                ))}
                <span className="text-xs ml-2" style={{ color: 'var(--color-dark-soft)' }}>(Uso Profesional)</span>
              </div>

              {/* Decorative line */}
              <div
                className="mb-6"
                style={{ width: '50px', height: '2px', background: 'var(--color-primary)' }}
              />

              {/* Description */}
              {product.description && (
                <p
                  className="text-sm leading-relaxed mb-6"
                  style={{ color: 'var(--color-dark-soft)' }}
                >
                  {product.description}
                </p>
              )}

              {/* Meta info */}
              <div
                className="flex flex-col gap-2 mb-6 p-4"
                style={{ background: 'var(--color-cream)', border: '1px solid var(--color-border)' }}
              >
                {product.sku && (
                  <div className="flex items-center gap-2 text-xs" style={{ color: 'var(--color-dark-soft)' }}>
                    <Tag size={13} style={{ color: 'var(--color-primary)' }} />
                    <span>SKU: <strong style={{ color: 'var(--color-dark)' }}>{product.sku}</strong></span>
                  </div>
                )}
                <div className="flex items-center gap-2 text-xs" style={{ color: 'var(--color-dark-soft)' }}>
                  <Package size={13} style={{ color: inStock ? '#2E7D52' : '#C25B56' }} />
                  <span>
                    {inStock ? (
                      <strong style={{ color: '#2E7D52' }}>En stock ({product.current_stock} unidades)</strong>
                    ) : (
                      <strong style={{ color: '#C25B56' }}>Sin stock</strong>
                    )}
                  </span>
                </div>
              </div>

              {/* Price + CTA */}
              <div className="flex items-center gap-5 mb-8">
                <span
                  style={{
                    fontFamily: 'var(--font-display)',
                    fontSize: '2rem',
                    fontWeight: 700,
                    color: 'var(--color-dark)',
                  }}
                >
                  {price}
                </span>
              </div>

              <div className="flex gap-3">
                <button
                  className="flex-1 flex items-center justify-center gap-2 py-4 text-sm font-bold uppercase tracking-widest transition-all"
                  style={{
                    background: inStock ? 'var(--color-dark)' : '#ccc',
                    color: 'var(--color-white)',
                    cursor: inStock ? 'pointer' : 'not-allowed',
                  }}
                  disabled={!inStock}
                >
                  <ShoppingBag size={16} />
                  {inStock ? 'Agregar al carrito' : 'Sin stock'}
                </button>
                <Link
                  href="/productos"
                  className="flex items-center gap-2 px-5 py-4 text-sm font-semibold uppercase tracking-wider"
                  style={{ border: '1px solid var(--color-dark)', color: 'var(--color-dark)' }}
                >
                  <ArrowLeft size={14} />
                  Volver
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Related products */}
      {related.length > 0 && (
        <section style={{ background: 'var(--color-cream)', padding: '60px 0' }}>
          <div className="max-w-7xl mx-auto px-6">
            <h2
              className="mb-10 text-center"
              style={{
                fontFamily: 'var(--font-display)',
                fontSize: '1.75rem',
                color: 'var(--color-dark)',
                fontStyle: 'italic',
              }}
            >
              Productos Relacionados
            </h2>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-5">
              {related.map((p) => (
                <ProductCard key={p.id} product={p} />
              ))}
            </div>
          </div>
        </section>
      )}
    </>
  )
}
