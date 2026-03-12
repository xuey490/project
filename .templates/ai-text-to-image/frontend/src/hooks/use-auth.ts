import { useState } from 'react';
import { supabase } from '@/lib/supabase';

interface AuthError {
  message: string;
}

export function useAuth() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<AuthError | null>(null);

  const signUp = async (email: string, password: string) => {
    setLoading(true);
    setError(null);
    
    const { data, error } = await supabase.auth.signUp({
      email,
      password,
    });
    
    setLoading(false);
    
    if (error) {
      setError({ message: error.message });
      return { success: false, error };
    }
    
    return { success: true, data };
  };

  const signIn = async (email: string, password: string) => {
    setLoading(true);
    setError(null);
    
    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password,
    });
    
    setLoading(false);
    
    if (error) {
      setError({ message: error.message });
      return { success: false, error };
    }
    
    return { success: true, data };
  };

  const signInWithGoogle = async () => {
    setLoading(true);
    setError(null);
    
    const { data, error } = await supabase.auth.signInWithOAuth({
      provider: 'google',
      options: {
        redirectTo: `${window.location.origin}/login`,
      },
    });
    
    setLoading(false);
    
    if (error) {
      setError({ message: error.message });
      return { success: false, error };
    }
    
    return { success: true, data };
  };

  const signOut = async () => {
    setLoading(true);
    setError(null);
    
    const { error } = await supabase.auth.signOut();
    
    setLoading(false);
    
    if (error) {
      setError({ message: error.message });
      return { success: false, error };
    }
    
    return { success: true };
  };

  const resetPassword = async (email: string) => {
    setLoading(true);
    setError(null);
    
    const { error } = await supabase.auth.resetPasswordForEmail(email, {
      redirectTo: `${window.location.origin}/reset-password`,
    });
    
    setLoading(false);
    
    if (error) {
      setError({ message: error.message });
      return { success: false, error };
    }
    
    return { success: true };
  };

  const clearError = () => setError(null);

  return {
    loading,
    error,
    signUp,
    signIn,
    signInWithGoogle,
    signOut,
    resetPassword,
    clearError,
  };
}
