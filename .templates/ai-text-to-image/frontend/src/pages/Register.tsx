import { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { Layout } from '@/components/layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAuth } from '@/hooks/use-auth';
import { useAuthContext } from '@/contexts/AuthContext';
import { Mail, Lock, Loader2, AlertCircle, CheckCircle } from 'lucide-react';

const Register = () => {
  const navigate = useNavigate();
  const { user, loading: authLoading } = useAuthContext();
  const { signUp, signInWithGoogle, loading, error, clearError } = useAuth();
  
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [success, setSuccess] = useState(false);
  const [validationError, setValidationError] = useState('');

  // 已登录则跳转首页
  useEffect(() => {
    if (!authLoading && user) {
      navigate('/');
    }
  }, [authLoading, user, navigate]);

  const validateForm = () => {
    if (password.length < 6) {
      setValidationError('密码至少需要6位');
      return false;
    }
    if (password !== confirmPassword) {
      setValidationError('两次输入的密码不一致');
      return false;
    }
    setValidationError('');
    return true;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    clearError();
    
    if (!email || !password || !confirmPassword) return;
    if (!validateForm()) return;
    
    const result = await signUp(email, password);
    if (result.success) {
      setSuccess(true);
    }
  };

  const handleGoogleRegister = async () => {
    await signInWithGoogle();
  };

  if (authLoading) {
    return (
      <Layout hideFooter>
        <div className="min-h-[calc(100vh-60px)] flex items-center justify-center">
          <Loader2 className="w-6 h-6 animate-spin text-primary" />
        </div>
      </Layout>
    );
  }

  // 注册成功提示
  if (success) {
    return (
      <Layout hideFooter>
        <div className="min-h-[calc(100vh-60px)] flex items-center justify-center py-12 px-4">
          <div className="w-full max-w-[360px] text-center">
            <div className="w-16 h-16 rounded-full bg-success/10 flex items-center justify-center mx-auto mb-4">
              <CheckCircle className="w-8 h-8 text-success" strokeWidth={1.5} />
            </div>
            <h1 className="text-xl font-semibold text-foreground mb-2">
              注册成功
            </h1>
            <p className="text-sm text-muted-foreground mb-6">
              我们已向 {email} 发送了验证邮件，请查收并点击链接完成验证
            </p>
            <Link to="/login">
              <Button className="w-full">
                返回登录
              </Button>
            </Link>
          </div>
        </div>
      </Layout>
    );
  }

  return (
    <Layout hideFooter>
      <div className="min-h-[calc(100vh-60px)] flex items-center justify-center py-12 px-4">
        <div className="w-full max-w-[360px]">
          {/* Header */}
          <div className="text-center mb-8">
            <h1 className="text-xl font-semibold text-foreground mb-2">
              创建账号
            </h1>
            <p className="text-sm text-muted-foreground">
              注册后可保存和管理你的作品
            </p>
          </div>

          {/* Register Form */}
          <div className="bg-card rounded-xl border border-border p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <form onSubmit={handleSubmit} className="space-y-4">
              {/* Email */}
              <div className="space-y-2">
                <Label htmlFor="email" className="text-xs text-muted-foreground">
                  邮箱
                </Label>
                <div className="relative">
                  <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" strokeWidth={1.5} />
                  <Input
                    id="email"
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="your@email.com"
                    className="pl-10 h-10"
                    disabled={loading}
                  />
                </div>
              </div>

              {/* Password */}
              <div className="space-y-2">
                <Label htmlFor="password" className="text-xs text-muted-foreground">
                  密码
                </Label>
                <div className="relative">
                  <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" strokeWidth={1.5} />
                  <Input
                    id="password"
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="至少6位密码"
                    className="pl-10 h-10"
                    disabled={loading}
                  />
                </div>
              </div>

              {/* Confirm Password */}
              <div className="space-y-2">
                <Label htmlFor="confirmPassword" className="text-xs text-muted-foreground">
                  确认密码
                </Label>
                <div className="relative">
                  <Lock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" strokeWidth={1.5} />
                  <Input
                    id="confirmPassword"
                    type="password"
                    value={confirmPassword}
                    onChange={(e) => setConfirmPassword(e.target.value)}
                    placeholder="再次输入密码"
                    className="pl-10 h-10"
                    disabled={loading}
                  />
                </div>
              </div>

              {/* Error Message */}
              {(error || validationError) && (
                <div className="flex items-center gap-2 p-3 rounded-lg bg-destructive/10 text-destructive text-xs">
                  <AlertCircle className="w-4 h-4 shrink-0" />
                  <span>{validationError || error?.message}</span>
                </div>
              )}

              {/* Submit Button */}
              <Button
                type="submit"
                className="w-full h-10"
                disabled={loading || !email || !password || !confirmPassword}
              >
                {loading ? (
                  <>
                    <Loader2 className="w-4 h-4 animate-spin mr-2" />
                    注册中...
                  </>
                ) : (
                  '注册'
                )}
              </Button>
            </form>

            {/* Divider */}
            <div className="relative my-6">
              <div className="absolute inset-0 flex items-center">
                <div className="w-full border-t border-border" />
              </div>
              <div className="relative flex justify-center text-xs">
                <span className="bg-card px-2 text-muted-foreground">或</span>
              </div>
            </div>

            {/* Google Register */}
            <Button
              type="button"
              variant="outline"
              className="w-full h-10 gap-2"
              onClick={handleGoogleRegister}
              disabled={loading}
            >
              <svg className="w-4 h-4" viewBox="0 0 24 24">
                <path
                  fill="currentColor"
                  d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                />
                <path
                  fill="currentColor"
                  d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                />
                <path
                  fill="currentColor"
                  d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                />
                <path
                  fill="currentColor"
                  d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                />
              </svg>
              使用 Google 注册
            </Button>

            {/* Login Link */}
            <p className="text-center text-xs text-muted-foreground mt-6">
              已有账号？{' '}
              <Link to="/login" className="text-primary hover:underline">
                立即登录
              </Link>
            </p>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default Register;
