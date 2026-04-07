export type AuditLog = {
  id: number;
  event: string;
  description: string;
  causer: {
    id: number;
    type: string;
    name: string;
    email: string;
    roles: string[];
  };
  session: {
    id: string;
    ip: string;
    user_agent: string;
  };
  changes?: {
    attributes?: Record<string, any>;
    old?: Record<string, any>;
  };
  created_at: string;
};
