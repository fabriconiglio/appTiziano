'use client'

import { createContext, useContext, useState, useEffect, useCallback, useMemo, type ReactNode } from 'react'
import { User } from './types'
import { loginUser as apiLogin, registerUser as apiRegister, logoutUser as apiLogout, getMe, googleLogin as apiGoogleLogin } from './api'

interface AuthContextType {
  user: User | null
  token: string | null
  loading: boolean
  isAuthenticated: boolean
  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>
  loginWithGoogle: (credential: string) => Promise<void>
  logout: () => Promise<void>
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

const TOKEN_KEY = 'tiziano_auth_token'
const USER_KEY = 'tiziano_auth_user'

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [token, setToken] = useState<string | null>(null)
  const [loading, setLoading] = useState(true)

  const persist = useCallback((u: User, t: string) => {
    setUser(u)
    setToken(t)
    localStorage.setItem(TOKEN_KEY, t)
    localStorage.setItem(USER_KEY, JSON.stringify(u))
  }, [])

  const clear = useCallback(() => {
    setUser(null)
    setToken(null)
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(USER_KEY)
  }, [])

  useEffect(() => {
    const savedToken = localStorage.getItem(TOKEN_KEY)
    if (!savedToken) {
      setLoading(false)
      return
    }
    getMe(savedToken)
      .then((u) => {
        setUser(u)
        setToken(savedToken)
        localStorage.setItem(USER_KEY, JSON.stringify(u))
      })
      .catch(() => {
        clear()
      })
      .finally(() => setLoading(false))
  }, [clear])

  const login = useCallback(async (email: string, password: string) => {
    const res = await apiLogin(email, password)
    persist(res.user, res.token)
  }, [persist])

  const register = useCallback(async (name: string, email: string, password: string, passwordConfirmation: string) => {
    const res = await apiRegister(name, email, password, passwordConfirmation)
    persist(res.user, res.token)
  }, [persist])

  const loginWithGoogle = useCallback(async (credential: string) => {
    const res = await apiGoogleLogin(credential)
    persist(res.user, res.token)
  }, [persist])

  const logout = useCallback(async () => {
    if (token) {
      try { await apiLogout(token) } catch { /* token may already be invalid */ }
    }
    clear()
  }, [token, clear])

  const value = useMemo(
    () => ({ user, token, loading, isAuthenticated: !!user, login, register, loginWithGoogle, logout }),
    [user, token, loading, login, register, loginWithGoogle, logout],
  )

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth(): AuthContextType {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
