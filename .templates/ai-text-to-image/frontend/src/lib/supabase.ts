import { createClient } from '@supabase/supabase-js';

const SUPABASE_URL = 'https://jvyddfsojtsipmiwjcdr.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imp2eWRkZnNvanRzaXBtaXdqY2RyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjIxNDE3NTAsImV4cCI6MjA3NzcxNzc1MH0.BvEXIgONC2GxAUzRIx8I7P3PsTH3p10vh68CEyRPRMg';

export const supabase = createClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
  auth: {
    autoRefreshToken: true,
    persistSession: true,
    detectSessionInUrl: true,
  },
});
