import { useState, useRef, useEffect } from 'react'
import { Link, useSearchParams, useNavigate } from 'react-router-dom'
import {
  User,
  Mail,
  Phone,
  Lock,
  Camera,
  Trash2,
  CheckCircle,
  AlertCircle,
  Loader2,
  Eye,
  EyeOff,
  Edit2,
  Save,
  X,
  ShieldCheck,
  RefreshCw,
  BadgeCheck,
  Clock,
  KeyRound,
  LogOut,
} from 'lucide-react'
import { useAuth } from '../contexts/AuthContext'
import {
  updateProfile,
  uploadAvatar,
  removeAvatar,
  requestEmailChange,
  verifyEmailChange,
  resendEmailVerification,
  requestPhoneChange,
  verifyPhoneChange,
  changePassword,
} from '../api/profile'

/* ─── helpers ───────────────────────────────────────────────────────────── */
function dicebearUrl(seed) {
  const s = encodeURIComponent(seed || 'user')
  return `https://api.dicebear.com/7.x/avataaars/svg?seed=${s}&backgroundColor=b6e3f4,c0aede,d1d4f9,ffd5dc,ffdfbf`
}

function Alert({ type = 'error', children }) {
  const styles = {
    error: 'bg-red-50 border-red-200 text-red-700',
    success: 'bg-green-50 border-green-200 text-green-700',
    info: 'bg-blue-50 border-blue-200 text-blue-700',
    warn: 'bg-amber-50 border-amber-200 text-amber-700',
  }
  const Icon = type === 'success' ? CheckCircle : type === 'info' ? ShieldCheck : AlertCircle
  return (
    <div className={`flex items-start gap-2.5 rounded-xl border px-4 py-3 text-sm ${styles[type]}`}>
      <Icon size={15} className="mt-0.5 shrink-0" />
      <div>{children}</div>
    </div>
  )
}

function Section({ title, icon: Icon, children }) {
  return (
    <div className="card p-6">
      <h2 className="flex items-center gap-2 text-base font-bold text-gray-800 mb-5">
        <Icon size={17} className="text-primary-500" />
        {title}
      </h2>
      {children}
    </div>
  )
}

function VerifiedBadge({ verified, label }) {
  return verified ? (
    <span className="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-full px-2.5 py-1">
      <BadgeCheck size={11} /> {label} Verified
    </span>
  ) : (
    <span className="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 rounded-full px-2.5 py-1">
      <Clock size={11} /> {label} Not Verified
    </span>
  )
}

/* ─── main page ─────────────────────────────────────────────────────────── */
export default function ProfilePage() {
  const { user, refreshUser, logout } = useAuth()
  const navigate = useNavigate()
  const [params] = useSearchParams()
  const fileRef = useRef(null)

  // ── Name ──
  const [firstName, setFirstName] = useState(user?.first_name ?? '')
  const [lastName, setLastName] = useState(user?.last_name ?? '')
  const [nameMsg, setNameMsg] = useState(null)
  const [nameSaving, setNameSaving] = useState(false)

  // ── Avatar ──
  const [avatarPreview, setAvatarPreview] = useState(null)
  const [avatarLoading, setAvatarLoading] = useState(false)
  const [avatarMsg, setAvatarMsg] = useState(null)

  // ── Email ──
  const [newEmail, setNewEmail] = useState('')
  const [emailMsg, setEmailMsg] = useState(null)
  const [emailLoading, setEmailLoading] = useState(false)
  const [emailSent, setEmailSent] = useState(false)

  // ── Phone ──
  const [newPhone, setNewPhone] = useState('')
  const [phoneOtp, setPhoneOtp] = useState('')
  const [phoneStep, setPhoneStep] = useState('edit') // 'edit' | 'otp'
  const [phoneMsg, setPhoneMsg] = useState(null)
  const [phoneLoading, setPhoneLoading] = useState(false)

  // ── Password ──
  const [pwCurrent, setPwCurrent] = useState('')
  const [pwNew, setPwNew] = useState('')
  const [pwConfirm, setPwConfirm] = useState('')
  const [pwShow, setPwShow] = useState(false)
  const [pwMsg, setPwMsg] = useState(null)
  const [pwLoading, setPwLoading] = useState(false)

  // Handle email verify link landing (/profile?verify=token&email=...)
  useEffect(() => {
    const token = params.get('token')
    const email = params.get('email')
    if (token && email && user) {
      verifyEmailChange(token, email)
        .then((res) => {
          refreshUser()
          setEmailMsg({ type: 'success', text: res.message ?? 'Email verified successfully!' })
        })
        .catch((err) =>
          setEmailMsg({
            type: 'error',
            text: err.response?.data?.message ?? 'Verification failed.',
          })
        )
    }
  }, []) // eslint-disable-line

  if (!user) {
    return (
      <div className="min-h-[60vh] flex flex-col items-center justify-center gap-4">
        <p className="text-gray-500">Please log in to view your profile.</p>
        <Link to="/login" className="btn-primary">
          Login
        </Link>
      </div>
    )
  }

  const displayName =
    `${user.first_name ?? ''} ${user.last_name ?? ''}`.trim() || user.name || 'User'
  const avatarSrc = avatarPreview ?? user.avatar ?? null

  /* ── handlers ── */
  const handleSaveName = async () => {
    setNameSaving(true)
    setNameMsg(null)
    try {
      await updateProfile({ first_name: firstName, last_name: lastName })
      await refreshUser()
      setNameMsg({ type: 'success', text: 'Name updated successfully.' })
    } catch (e) {
      setNameMsg({ type: 'error', text: e.response?.data?.message ?? 'Failed to update name.' })
    } finally {
      setNameSaving(false)
    }
  }

  const handleAvatarChange = async (e) => {
    const file = e.target.files?.[0]
    if (!file) return
    setAvatarPreview(URL.createObjectURL(file))
    setAvatarLoading(true)
    setAvatarMsg(null)
    try {
      await uploadAvatar(file)
      await refreshUser()
      setAvatarMsg({ type: 'success', text: 'Photo updated!' })
    } catch (e) {
      setAvatarMsg({ type: 'error', text: e.response?.data?.message ?? 'Upload failed.' })
      setAvatarPreview(null)
    } finally {
      setAvatarLoading(false)
    }
  }

  const handleRemoveAvatar = async () => {
    setAvatarLoading(true)
    setAvatarMsg(null)
    try {
      await removeAvatar()
      await refreshUser()
      setAvatarPreview(null)
      setAvatarMsg({ type: 'success', text: 'Photo removed.' })
    } catch (e) {
      setAvatarMsg({ type: 'error', text: 'Failed to remove photo.' })
    } finally {
      setAvatarLoading(false)
    }
  }

  const handleRequestEmail = async () => {
    setEmailLoading(true)
    setEmailMsg(null)
    try {
      await requestEmailChange(newEmail)
      setEmailSent(true)
      setEmailMsg({
        type: 'success',
        text: `Verification link sent to ${newEmail}. Check your inbox.`,
      })
    } catch (e) {
      setEmailMsg({
        type: 'error',
        text: e.response?.data?.message ?? 'Failed to send verification.',
      })
    } finally {
      setEmailLoading(false)
    }
  }

  const handleResendVerification = async () => {
    setEmailLoading(true)
    setEmailMsg(null)
    try {
      await resendEmailVerification()
      setEmailMsg({ type: 'success', text: 'Verification email resent. Check your inbox.' })
    } catch (e) {
      setEmailMsg({ type: 'error', text: e.response?.data?.message ?? 'Failed to resend.' })
    } finally {
      setEmailLoading(false)
    }
  }

  const handleRequestPhone = async () => {
    setPhoneLoading(true)
    setPhoneMsg(null)
    try {
      await requestPhoneChange(newPhone)
      setPhoneStep('otp')
      setPhoneMsg({ type: 'info', text: `OTP sent to ${newPhone}.` })
    } catch (e) {
      setPhoneMsg({ type: 'error', text: e.response?.data?.message ?? 'Failed to send OTP.' })
    } finally {
      setPhoneLoading(false)
    }
  }

  const handleVerifyPhone = async () => {
    setPhoneLoading(true)
    setPhoneMsg(null)
    try {
      await verifyPhoneChange(phoneOtp)
      await refreshUser()
      setPhoneStep('edit')
      setNewPhone('')
      setPhoneOtp('')
      setPhoneMsg({ type: 'success', text: 'Phone number updated and verified!' })
    } catch (e) {
      setPhoneMsg({ type: 'error', text: e.response?.data?.message ?? 'Invalid OTP.' })
    } finally {
      setPhoneLoading(false)
    }
  }

  const handleChangePassword = async () => {
    if (pwNew !== pwConfirm) {
      setPwMsg({ type: 'error', text: 'Passwords do not match.' })
      return
    }
    setPwLoading(true)
    setPwMsg(null)
    try {
      await changePassword({
        current_password: pwCurrent,
        password: pwNew,
        password_confirmation: pwConfirm,
      })
      setPwCurrent('')
      setPwNew('')
      setPwConfirm('')
      setPwMsg({ type: 'success', text: 'Password changed successfully.' })
    } catch (e) {
      setPwMsg({ type: 'error', text: e.response?.data?.message ?? 'Failed to change password.' })
    } finally {
      setPwLoading(false)
    }
  }

  return (
    <div className="max-w-2xl mx-auto px-4 sm:px-6 py-10">
      <h1 className="text-2xl font-extrabold text-gray-900 mb-8">My Profile</h1>

      {/* ── Avatar + Name hero ── */}
      <div className="card p-6 mb-6">
        <div className="flex flex-col sm:flex-row items-center sm:items-start gap-6">
          {/* Avatar */}
          <div className="relative shrink-0">
            <div className="w-24 h-24 rounded-2xl overflow-hidden bg-gradient-to-br from-primary-100 to-primary-200 border-2 border-primary-200 shadow-md">
              {avatarSrc ? (
                <img src={avatarSrc} alt={displayName} className="w-full h-full object-cover" />
              ) : (
                <img
                  src={dicebearUrl(displayName)}
                  alt={displayName}
                  className="w-full h-full object-cover"
                />
              )}
            </div>
            {/* Camera button */}
            <button
              onClick={() => fileRef.current?.click()}
              disabled={avatarLoading}
              className="absolute -bottom-2 -right-2 w-8 h-8 bg-primary-500 hover:bg-primary-600 text-white rounded-xl flex items-center justify-center shadow-md transition-colors"
              title="Upload photo"
            >
              {avatarLoading ? (
                <Loader2 size={13} className="animate-spin" />
              ) : (
                <Camera size={13} />
              )}
            </button>
            <input
              ref={fileRef}
              type="file"
              accept="image/*"
              className="hidden"
              onChange={handleAvatarChange}
            />
          </div>

          {/* Info */}
          <div className="flex-1 text-center sm:text-left">
            <p className="text-xl font-extrabold text-gray-900">{displayName}</p>
            <p className="text-sm text-gray-500 mt-0.5">{user.email}</p>
            {user.member_since && (
              <p className="text-xs text-gray-400 mt-1">
                Member since{' '}
                {new Date(user.member_since).toLocaleDateString('en-IN', {
                  month: 'long',
                  year: 'numeric',
                })}
              </p>
            )}
            <div className="flex gap-2 mt-3 justify-center sm:justify-start flex-wrap">
              <VerifiedBadge verified={user.email_verified} label="Email" />
              {user.phone && <VerifiedBadge verified={user.phone_verified} label="Phone" />}
            </div>
            {(user.avatar || avatarPreview) && (
              <button
                onClick={handleRemoveAvatar}
                disabled={avatarLoading}
                className="mt-3 text-xs text-red-400 hover:text-red-600 flex items-center gap-1 transition-colors mx-auto sm:mx-0"
              >
                <Trash2 size={11} /> Remove photo
              </button>
            )}
          </div>
        </div>
        {avatarMsg && (
          <div className="mt-4">
            <Alert type={avatarMsg.type}>{avatarMsg.text}</Alert>
          </div>
        )}
      </div>

      {/* ── Name ── */}
      <Section title="Personal Information" icon={User}>
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1.5">First Name</label>
            <input
              value={firstName}
              onChange={(e) => setFirstName(e.target.value)}
              className="w-full border-2 border-gray-200 focus:border-primary-400 rounded-xl px-3 py-2.5 text-sm outline-none transition-all"
              placeholder="First name"
            />
          </div>
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1.5">Last Name</label>
            <input
              value={lastName}
              onChange={(e) => setLastName(e.target.value)}
              className="w-full border-2 border-gray-200 focus:border-primary-400 rounded-xl px-3 py-2.5 text-sm outline-none transition-all"
              placeholder="Last name"
            />
          </div>
        </div>
        {nameMsg && (
          <div className="mt-3">
            <Alert type={nameMsg.type}>{nameMsg.text}</Alert>
          </div>
        )}
        <button
          onClick={handleSaveName}
          disabled={nameSaving || (!firstName.trim() && !lastName.trim())}
          className="mt-4 btn-primary !py-2.5 !rounded-xl disabled:opacity-50"
        >
          {nameSaving ? (
            <>
              <Loader2 size={14} className="animate-spin" /> Saving…
            </>
          ) : (
            <>
              <Save size={14} /> Save Name
            </>
          )}
        </button>
      </Section>

      {/* ── Email ── */}
      <Section title="Email Address" icon={Mail}>
        <div className="space-y-4">
          <div className="rounded-xl border border-gray-200/80 bg-gray-50/80 p-4">
            <p className="text-xs font-medium uppercase tracking-wider text-gray-400 mb-1">
              Current email
            </p>
            <p className="text-base font-semibold text-gray-900 break-all">{user.email}</p>
            <div className="mt-2">
              <VerifiedBadge verified={user.email_verified} label="Email" />
            </div>
          </div>

          {!user.email_verified && (
            <Alert type="warn">
              Your email is not verified.{' '}
              <button
                onClick={handleResendVerification}
                disabled={emailLoading}
                className="font-semibold underline hover:no-underline ml-1"
              >
                {emailLoading ? 'Sending…' : 'Resend verification email'}
              </button>
            </Alert>
          )}

          <div className="pt-2 border-t border-gray-200/80">
            <p className="text-xs font-semibold text-gray-500 mb-2">Change email</p>
            {!emailSent ? (
              <div className="flex gap-2">
                <input
                  type="email"
                  value={newEmail}
                  onChange={(e) => setNewEmail(e.target.value)}
                  placeholder="new@email.com"
                  className="flex-1 border-2 border-gray-200 focus:border-primary-400 rounded-xl px-3 py-2.5 text-sm outline-none transition-all"
                />
                <button
                  onClick={handleRequestEmail}
                  disabled={emailLoading || !newEmail.trim()}
                  className="btn-primary !py-2 !px-4 !rounded-xl disabled:opacity-50 shrink-0"
                >
                  {emailLoading ? <Loader2 size={14} className="animate-spin" /> : 'Send Link'}
                </button>
              </div>
            ) : (
              <div className="flex items-center gap-2 flex-wrap">
                <p className="text-sm text-gray-500 flex-1 min-w-0">
                  Verification link sent to{' '}
                  <span className="font-semibold text-gray-800">{newEmail}</span>
                </p>
                <button
                  onClick={() => {
                    setEmailSent(false)
                    setNewEmail('')
                  }}
                  className="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1 shrink-0"
                >
                  <RefreshCw size={11} /> Change
                </button>
              </div>
            )}
          </div>

          {emailMsg && (
            <div className="mt-2">
              <Alert type={emailMsg.type}>{emailMsg.text}</Alert>
            </div>
          )}
        </div>
      </Section>

      {/* ── Phone ── */}
      <Section title="Phone Number" icon={Phone}>
        <div className="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 mb-4">
          <div>
            <p className="text-xs text-gray-400 mb-0.5">Current phone</p>
            <p className="text-sm font-semibold text-gray-800 font-mono">{user.phone ?? '—'}</p>
          </div>
          {user.phone && <VerifiedBadge verified={user.phone_verified} label="Phone" />}
        </div>

        {phoneStep === 'edit' ? (
          <>
            <label className="block text-xs font-semibold text-gray-500 mb-1.5">
              Change to new phone
            </label>
            <div className="flex gap-2">
              <input
                type="tel"
                value={newPhone}
                onChange={(e) => setNewPhone(e.target.value)}
                placeholder="+91 98765 43210"
                className="flex-1 border-2 border-gray-200 focus:border-primary-400 rounded-xl px-3 py-2.5 text-sm outline-none transition-all font-mono"
              />
              <button
                onClick={handleRequestPhone}
                disabled={phoneLoading || !newPhone.trim()}
                className="btn-primary !py-2 !px-4 !rounded-xl disabled:opacity-50 shrink-0"
              >
                {phoneLoading ? <Loader2 size={14} className="animate-spin" /> : 'Send OTP'}
              </button>
            </div>
          </>
        ) : (
          <>
            <label className="block text-xs font-semibold text-gray-500 mb-1.5">
              Enter the 6-digit OTP sent to {newPhone}
            </label>
            <div className="flex gap-2">
              <input
                type="text"
                inputMode="numeric"
                maxLength={6}
                value={phoneOtp}
                onChange={(e) => setPhoneOtp(e.target.value.replace(/\D/g, ''))}
                placeholder="123456"
                className="flex-1 border-2 border-gray-200 focus:border-primary-400 rounded-xl px-3 py-2.5 text-sm outline-none transition-all font-mono tracking-[0.3em] text-center"
              />
              <button
                onClick={handleVerifyPhone}
                disabled={phoneLoading || phoneOtp.length < 6}
                className="btn-primary !py-2 !px-4 !rounded-xl disabled:opacity-50 shrink-0"
              >
                {phoneLoading ? <Loader2 size={14} className="animate-spin" /> : 'Verify'}
              </button>
              <button
                onClick={() => {
                  setPhoneStep('edit')
                  setPhoneOtp('')
                  setDevOtp('')
                }}
                className="p-2 border-2 border-gray-200 rounded-xl hover:bg-gray-50 text-gray-500 transition-colors shrink-0"
              >
                <X size={14} />
              </button>
            </div>
            <button
              onClick={handleRequestPhone}
              disabled={phoneLoading}
              className="mt-2 text-xs text-primary-500 hover:text-primary-600 flex items-center gap-1"
            >
              <RefreshCw size={10} /> Resend OTP
            </button>
          </>
        )}

        {phoneMsg && (
          <div className="mt-3">
            <Alert type={phoneMsg.type}>{phoneMsg.text}</Alert>
          </div>
        )}
      </Section>

      {/* ── Password ── */}
      <Section title="Change Password" icon={KeyRound}>
        <div className="space-y-3">
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1.5">
              Current Password
            </label>
            <div className="relative">
              <input
                type={pwShow ? 'text' : 'password'}
                value={pwCurrent}
                onChange={(e) => setPwCurrent(e.target.value)}
                placeholder="Your current password"
                className="w-full pr-10 border-2 border-gray-200 focus:border-primary-400 rounded-xl px-3 py-2.5 text-sm outline-none transition-all"
              />
              <button
                type="button"
                onClick={() => setPwShow((v) => !v)}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                tabIndex={-1}
              >
                {pwShow ? <EyeOff size={15} /> : <Eye size={15} />}
              </button>
            </div>
          </div>
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1.5">New Password</label>
            <input
              type={pwShow ? 'text' : 'password'}
              value={pwNew}
              onChange={(e) => setPwNew(e.target.value)}
              placeholder="Minimum 8 characters"
              className="w-full border-2 border-gray-200 focus:border-primary-400 rounded-xl px-3 py-2.5 text-sm outline-none transition-all"
            />
          </div>
          <div>
            <label className="block text-xs font-semibold text-gray-500 mb-1.5">
              Confirm New Password
            </label>
            <input
              type={pwShow ? 'text' : 'password'}
              value={pwConfirm}
              onChange={(e) => setPwConfirm(e.target.value)}
              placeholder="Repeat new password"
              className="w-full border-2 border-gray-200 focus:border-primary-400 rounded-xl px-3 py-2.5 text-sm outline-none transition-all"
            />
          </div>
        </div>
        {pwMsg && (
          <div className="mt-3">
            <Alert type={pwMsg.type}>{pwMsg.text}</Alert>
          </div>
        )}
        <button
          onClick={handleChangePassword}
          disabled={pwLoading || !pwCurrent || !pwNew || !pwConfirm}
          className="mt-4 btn-primary !py-2.5 !rounded-xl disabled:opacity-50"
        >
          {pwLoading ? (
            <>
              <Loader2 size={14} className="animate-spin" /> Changing…
            </>
          ) : (
            <>
              <Lock size={14} /> Change Password
            </>
          )}
        </button>
      </Section>

      {/* ── Danger zone ── */}
      <div className="card p-6 border border-red-100">
        <h2 className="text-base font-bold text-red-600 mb-3 flex items-center gap-2">
          <LogOut size={17} /> Sign Out
        </h2>
        <p className="text-sm text-gray-500 mb-4">
          Sign out of your PayFlex account on this device.
        </p>
        <button
          onClick={async () => {
            await logout()
            navigate('/login')
          }}
          className="px-5 py-2.5 rounded-xl border-2 border-red-200 text-red-600 text-sm font-semibold hover:bg-red-50 transition-colors"
        >
          Sign Out
        </button>
      </div>
    </div>
  )
}
