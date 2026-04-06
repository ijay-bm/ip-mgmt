export type User = {
  id: number;
  name: string;
  email: string;
  created_at: string;
  updated_at: string;
  roles: string[];
};

export type Token = {
  access_token: string;
  token_type: string;
  expires_in: number;
};
